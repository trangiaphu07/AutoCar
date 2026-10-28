<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    public const GATEWAYS = [
        'vnpay' => 'VNPay',
        'showroom' => 'Tại showroom',
        'bank_transfer' => 'Chuyển khoản',
    ];

    protected $fillable = [
        'order_id', 'gateway', 'transaction_code', 'amount', 'status',
        'gateway_transaction_no', 'raw_response', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'raw_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function gatewayLabel(): string
    {
        return self::GATEWAYS[$this->gateway] ?? $this->gateway;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['success', 'failed'], true);
    }
}
