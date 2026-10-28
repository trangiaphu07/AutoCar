<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    public function isMock(): bool
    {
        return blank(config('autoshop.vnpay.tmn_code')) || blank(config('autoshop.vnpay.hash_secret'));
    }

    /** URL chuyển khách sang cổng thanh toán (VNPay thật hoặc cổng giả lập). */
    public function paymentUrl(PaymentTransaction $tx, string $ip): string
    {
        if ($this->isMock()) {
            return route('payment.mock', $tx->transaction_code);
        }

        $params = [
            'vnp_Version' => config('autoshop.vnpay.version'),
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => config('autoshop.vnpay.tmn_code'),
            'vnp_Amount' => $tx->amount * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $tx->transaction_code,
            'vnp_OrderInfo' => 'Dat coc don hang '.$tx->order->code,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => route('payment.vnpay.return'),
            'vnp_IpAddr' => $ip,
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_ExpireDate' => now()->addMinutes((int) config('autoshop.payment_expire_minutes'))->format('YmdHis'),
        ];

        ksort($params);
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC1738);
        $hash = hash_hmac('sha512', $query, (string) config('autoshop.vnpay.hash_secret'));

        return config('autoshop.vnpay.url').'?'.$query.'&vnp_SecureHash='.$hash;
    }

    /** Kiểm tra chữ ký HMAC-SHA512 của dữ liệu VNPay gửi về. */
    public function verifyVnpay(array $input): bool
    {
        $hash = (string) ($input['vnp_SecureHash'] ?? '');

        $data = collect($input)
            ->filter(fn ($v, $k) => str_starts_with($k, 'vnp_') && ! in_array($k, ['vnp_SecureHash', 'vnp_SecureHashType'], true))
            ->sortKeys()
            ->all();

        $expected = hash_hmac('sha512', http_build_query($data, '', '&', PHP_QUERY_RFC1738), (string) config('autoshop.vnpay.hash_secret'));

        return $hash !== '' && hash_equals($expected, $hash);
    }

    /**
     * Ghi nhận kết quả thanh toán MỘT LẦN (idempotent).
     * Trả về: ok | already | wrong_amount | not_found
     */
    public function settle(string $code, int $amount, bool $success, ?string $gatewayNo, array $raw): string
    {
        return DB::transaction(function () use ($code, $amount, $success, $gatewayNo, $raw) {
            $tx = PaymentTransaction::where('transaction_code', $code)->lockForUpdate()->first();

            if (! $tx) {
                return 'not_found';
            }
            if ((int) $tx->amount !== $amount) {
                return 'wrong_amount';
            }
            if ($tx->isFinal()) {
                return 'already'; // IPN gửi lặp -> bỏ qua, không ghi nhận hai lần
            }

            $tx->update([
                'status' => $success ? 'success' : 'failed',
                'gateway_transaction_no' => $gatewayNo,
                'raw_response' => $raw,
                'paid_at' => $success ? now() : null,
            ]);

            $order = Order::lockForUpdate()->find($tx->order_id);

            if ($order->status === 'cancelled') {
                return 'ok'; // đơn đã hủy (quá hạn) – giữ log để đối soát/hoàn tiền thủ công
            }

            if ($success) {
                $order->payment_status = $tx->amount >= $order->total ? 'paid' : 'deposited';
                if ($order->status === 'pending') {
                    $order->status = 'confirmed';
                }
            } else {
                $order->payment_status = 'failed';
            }
            $order->save();

            return 'ok';
        });
    }

    /** Ảnh mã VietQR (img.vietqr.io) đã điền sẵn tài khoản, số tiền và nội dung chuyển khoản. */
    public function vietQrUrl(PaymentTransaction $tx): string
    {
        $bank = config('autoshop.bank');

        return sprintf('https://img.vietqr.io/image/%s-%s-%s.png', rawurlencode($bank['bank_id']), rawurlencode($bank['account_no']), $bank['qr_template'])
            .'?'.http_build_query([
                'amount' => $tx->amount,
                'addInfo' => $tx->transaction_code,
                'accountName' => $bank['account_name'],
            ], '', '&', PHP_QUERY_RFC3986);
    }

    /** Giao dịch chuyển khoản đang chờ đối soát của đơn (nếu có). */
    public function pendingTransfer(Order $order): ?PaymentTransaction
    {
        return $order->transactions()->where('gateway', 'bank_transfer')->where('status', 'pending')->first();
    }

    /** Admin xác nhận đã nhận tiền chuyển khoản: ghi nhận cọc và chuyển đơn sang "Đã xác nhận". */
    public function confirmTransfer(Order $order, User $admin, ?string $bankRef): void
    {
        if (! $order->awaitingTransfer() || ! ($tx = $this->pendingTransfer($order))) {
            throw new RuntimeException('Đơn này không có khoản chuyển khoản nào đang chờ xác nhận.');
        }

        $this->settle($tx->transaction_code, $tx->amount, true, $bankRef ?: null, [
            'confirmed_by' => $admin->email,
            'confirmed_at' => now()->toDateTimeString(),
        ]);
    }

    /** Tạo giao dịch mới để khách thử thanh toán lại một đơn bị lỗi/chưa trả. */
    public function retry(Order $order): PaymentTransaction
    {
        return $order->transactions()->create([
            'gateway' => 'vnpay',
            'transaction_code' => $order->code.strtoupper(\Illuminate\Support\Str::random(4)),
            'amount' => $order->deposit_amount,
            'status' => 'pending',
        ]);
    }
}
