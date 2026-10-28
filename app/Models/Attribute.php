<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Định nghĩa một thông số kỹ thuật theo dòng xe (mô hình EAV).
 */
class Attribute extends Model
{
    public const TYPES = ['text' => 'Văn bản', 'number' => 'Số', 'select' => 'Lựa chọn'];

    protected $fillable = [
        'category_id', 'code', 'name', 'type', 'unit', 'options',
        'is_required', 'is_filterable', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(CarAttributeValue::class);
    }

    public function label(): string
    {
        return $this->unit ? "{$this->name} ({$this->unit})" : $this->name;
    }
}
