<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'delivering' => 'Đang làm thủ tục / giao xe',
        'completed' => 'Đã bàn giao xe',
        'cancelled' => 'Đã hủy',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Chưa thanh toán',
        'deposited' => 'Đã đặt cọc',
        'paid' => 'Đã thanh toán đủ',
        'failed' => 'Thanh toán thất bại',
    ];

    public const PAYMENT_METHODS = [
        'showroom' => 'Đặt cọc tại showroom',
        'vnpay' => 'Đặt cọc online qua VNPay',
        'bank_transfer' => 'Chuyển khoản ngân hàng (VietQR)',
    ];

    /** Máy trạng thái: chỉ được chuyển theo các bước hợp lệ. */
    public const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['delivering', 'cancelled'],
        'delivering' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'code', 'user_id', 'customer_name', 'customer_phone', 'customer_email', 'customer_id_number',
        'address', 'showroom', 'note', 'subtotal', 'registration_fee', 'total', 'deposit_amount',
        'payment_method', 'payment_status', 'status', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'registration_fee' => 'integer',
            'total' => 'integer',
            'deposit_amount' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class)->latest('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function remainingAmount(): int
    {
        return match ($this->payment_status) {
            'paid' => 0,
            'deposited' => max(0, $this->total - $this->deposit_amount),
            default => $this->total,
        };
    }

    /** Đơn chuyển khoản đang chờ admin xác nhận đã nhận tiền cọc. */
    public function awaitingTransfer(): bool
    {
        return $this->payment_method === 'bank_transfer'
            && $this->status === 'pending'
            && in_array($this->payment_status, ['unpaid', 'failed'], true);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->status === 'pending' && in_array($this->payment_status, ['unpaid', 'failed'], true);
    }

    /**
     * Chuyển trạng thái (bọc transaction). Khi hủy: hoàn lại xe vào kho.
     */
    public function transitionTo(string $status): void
    {
        DB::transaction(function () use ($status) {
            $order = self::lockForUpdate()->findOrFail($this->id);

            if (! $order->canTransitionTo($status)) {
                throw new RuntimeException('Không thể chuyển đơn từ “'.$order->statusLabel().'” sang “'.(self::STATUSES[$status] ?? $status).'”.');
            }

            if ($status === 'cancelled') {
                $order->restock();
                $order->cancelled_at = now();
                $order->transactions()->where('status', 'pending')->update(['status' => 'failed']);
            }

            if ($status === 'completed') {
                $order->payment_status = 'paid';
            }

            $order->status = $status;
            $order->save();

            $this->setRawAttributes($order->getAttributes(), true);
        });
    }

    /** Cộng trả tồn kho cho từng dòng xe của đơn. */
    protected function restock(): void
    {
        foreach ($this->items as $item) {
            if ($item->car_id) {
                Car::withTrashed()->whereKey($item->car_id)->lockForUpdate()->first()?->increment('stock', $item->quantity);
            }
        }
    }
}
