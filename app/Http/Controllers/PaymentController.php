<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    /** Trình duyệt khách quay về từ VNPay. */
    public function vnpayReturn(Request $request)
    {
        $input = $request->query();

        if (! $this->payments->verifyVnpay($input)) {
            return redirect()->route('home')->with('error', 'Chữ ký thanh toán không hợp lệ.');
        }

        $tx = PaymentTransaction::with('order')->where('transaction_code', $input['vnp_TxnRef'] ?? '')->first();
        if (! $tx) {
            return redirect()->route('home')->with('error', 'Không tìm thấy giao dịch.');
        }

        $success = ($input['vnp_ResponseCode'] ?? '') === '00' && ($input['vnp_TransactionStatus'] ?? '00') === '00';
        $this->payments->settle($tx->transaction_code, intdiv((int) ($input['vnp_Amount'] ?? 0), 100), $success, $input['vnp_TransactionNo'] ?? null, $input);

        session()->push('my_orders', $tx->order->code);

        return redirect()->route('orders.result', $tx->order->code)
            ->with($success ? 'success' : 'error', $success ? 'Đặt cọc thành công qua VNPay!' : 'Thanh toán chưa thành công, bạn có thể thử lại.');
    }

    /** Máy chủ VNPay gọi xác nhận kết quả (nguồn tin cậy). */
    public function vnpayIpn(Request $request)
    {
        $input = $request->all();

        if (! $this->payments->verifyVnpay($input)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $success = ($input['vnp_ResponseCode'] ?? '') === '00' && ($input['vnp_TransactionStatus'] ?? '00') === '00';
        $result = $this->payments->settle(
            (string) ($input['vnp_TxnRef'] ?? ''),
            (int) (($input['vnp_Amount'] ?? 0) / 100),
            $success,
            $input['vnp_TransactionNo'] ?? null,
            $input,
        );

        return response()->json(match ($result) {
            'ok' => ['RspCode' => '00', 'Message' => 'Confirm Success'],
            'already' => ['RspCode' => '02', 'Message' => 'Order already confirmed'],
            'wrong_amount' => ['RspCode' => '04', 'Message' => 'Invalid amount'],
            default => ['RspCode' => '01', 'Message' => 'Order not found'],
        });
    }

    /* ---------- Cổng thanh toán giả lập (khi chưa cấu hình VNPay) ---------- */

    public function mock(string $code)
    {
        abort_unless($this->payments->isMock(), 404);
        $tx = PaymentTransaction::with('order.items')->where('transaction_code', $code)->firstOrFail();

        return view('payment.mock', compact('tx'));
    }

    public function mockSubmit(Request $request, string $code)
    {
        abort_unless($this->payments->isMock(), 404);
        $tx = PaymentTransaction::with('order')->where('transaction_code', $code)->firstOrFail();
        $success = $request->input('result') === 'success';

        $result = $this->payments->settle($tx->transaction_code, $tx->amount, $success, $success ? 'MOCK'.random_int(100000, 999999) : null, [
            'gateway' => 'mock', 'result' => $request->input('result'), 'at' => now()->toIso8601String(),
        ]);

        session()->push('my_orders', $tx->order->code);

        $msg = match (true) {
            $result === 'already' => ['error', 'Giao dịch này đã được xử lý trước đó.'],
            $success => ['success', 'Đặt cọc thành công! Đơn hàng đã được xác nhận.'],
            default => ['error', 'Thanh toán thất bại. Bạn có thể thanh toán lại trong 15 phút.'],
        };

        return redirect()->route('orders.result', $tx->order->code)->with($msg[0], $msg[1]);
    }
}
