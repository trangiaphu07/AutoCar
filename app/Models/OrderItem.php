<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'car_id', 'name', 'sku', 'color', 'year', 'price', 'quantity'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'quantity' => 'integer'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class)->withTrashed();
    }

    public function lineTotal(): int
    {
        return $this->price * $this->quantity;
    }
}
