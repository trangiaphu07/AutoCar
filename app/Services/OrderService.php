<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    public function __construct(private CartService $cart) {}

    /**
     * Đặt mua xe an toàn: transaction + khóa dòng (SELECT ... FOR UPDATE).
     * Nếu bất kỳ xe nào không đủ hàng thì toàn bộ đơn bị rollback.
     */
    public function place(array $data, ?int $userId): Order
    {
        $summary = $this->cart->summary();

        if ($summary['items']->isEmpty()) {
            throw new RuntimeException('Giỏ xe đang trống.');
        }

        $order = DB::transaction(function () use ($data, $userId, $summary) {
            $order = Order::create([
                'code' => $this->newCode(),
                'user_id' => $userId,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_id_number' => $data['customer_id_number'] ?? null,
                'address' => $data['address'],
                'showroom' => $data['showroom'],
                'note' => $data['note'] ?? null,
                'subtotal' => $summary['subtotal'],
                'registration_fee' => $summary['registration'],
                'total' => $summary['total'],
                'deposit_amount' => $summary['deposit'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'status' => 'pending',
            ]);

            foreach ($summary['items'] as $line) {
                $car = Car::lockForUpdate()->find($line->car->id);

                if (! $car || ! $car->isPurchasable() || $car->stock < $line->quantity) {
                    throw new RuntimeException('Xe “'.$line->car->name.'” chỉ còn '.($car?->stock ?? 0).' chiếc, vui lòng cập nhật lại giỏ.');
                }

                $car->decrement('stock', $line->quantity);

                $order->items()->create([
                    'car_id' => $car->id,
                    'name' => $car->name,
                    'sku' => $car->sku,
                    'color' => $car->color,
                    'year' => $car->year,
                    'price' => $car->effectivePrice(),
                    'quantity' => $line->quantity,
                ]);
            }

            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => $order->payment_method,
                'transaction_code' => $order->code.strtoupper(Str::random(4)),
                'amount' => $order->deposit_amount,
                'status' => 'pending',
            ]);

            return $order;
        });

        $this->cart->clear();

        return $order;
    }

    private function newCode(): string
    {
        do {
            $code = 'AS'.now()->format('ymd').strtoupper(Str::random(5));
        } while (Order::where('code', $code)->exists());

        return $code;
    }

    /** Hủy các đơn VNPay quá hạn chưa đặt cọc; trả xe về kho. */
    public function cancelExpired(): int
    {
        $minutes = (int) config('autoshop.payment_expire_minutes');
        $count = 0;

        Order::where('payment_method', 'vnpay')
            ->where('status', 'pending')
            ->whereIn('payment_status', ['unpaid', 'failed'])
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->each(function (Order $order) use (&$count) {
                try {
                    $order->transitionTo('cancelled');
                    $count++;
                } catch (RuntimeException) {
                    // đơn đã đổi trạng thái giữa chừng -> bỏ qua
                }
            });

        return $count;
    }
}
