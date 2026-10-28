<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestDrive extends Model
{
    public const STATUSES = [
        'new' => 'Mới đăng ký',
        'confirmed' => 'Đã xác nhận lịch',
        'done' => 'Đã lái thử',
        'cancelled' => 'Đã hủy',
    ];

    protected $fillable = [
        'car_id', 'user_id', 'name', 'phone', 'email', 'showroom',
        'preferred_date', 'preferred_time', 'note', 'status',
    ];

    protected function casts(): array
    {
        return ['preferred_date' => 'date'];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
