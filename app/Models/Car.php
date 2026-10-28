<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'draft' => 'Nháp',
        'review' => 'Chờ duyệt',
        'published' => 'Đang bán',
    ];

    public const CONDITIONS = [
        'new' => 'Xe mới',
        'used' => 'Xe đã qua sử dụng',
    ];

    protected $fillable = [
        'category_id', 'brand_id', 'sku', 'name', 'slug', 'year', 'condition', 'mileage',
        'color', 'color_hex', 'price', 'sale_price', 'stock', 'status', 'completeness',
        'is_featured', 'image', 'gallery', 'short_description', 'description', 'views',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
            'year' => 'integer',
            'mileage' => 'integer',
            'is_featured' => 'boolean',
            'gallery' => 'array',
        ];
    }

    /* ---------- Quan hệ ---------- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(CarAttributeValue::class);
    }

    public function testDrives(): HasMany
    {
        return $this->hasMany(TestDrive::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /* ---------- Scope ---------- */

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    /* ---------- Nghiệp vụ ---------- */

    /** Giá bán thực tế (ưu tiên giá khuyến mãi nếu hợp lệ). */
    public function effectivePrice(): int
    {
        return ($this->sale_price && $this->sale_price < $this->price) ? $this->sale_price : $this->price;
    }

    public function discountPercent(): int
    {
        if (! $this->sale_price || $this->sale_price >= $this->price) {
            return 0;
        }

        return (int) round(($this->price - $this->sale_price) / $this->price * 100);
    }

    public function isPurchasable(): bool
    {
        return $this->status === 'published' && $this->stock > 0;
    }

    public function imageUrl(): ?string
    {
        $img = $this->image;
        if (! $img && ! empty($this->gallery) && is_array($this->gallery)) {
            $img = $this->gallery[0] ?? null;
        }

        if (! $img) {
            return null;
        }

        return str_starts_with($img, 'http') ? $img : Storage::disk('public')->url($img);
    }

    /** Danh sách toàn bộ URL ảnh thực tế của xe (bao gồm ảnh chính và gallery). */
    public function galleryUrls(): array
    {
        $urls = [];
        if ($this->image) {
            $urls[] = str_starts_with($this->image, 'http') ? $this->image : Storage::disk('public')->url($this->image);
        }

        if (is_array($this->gallery)) {
            foreach ($this->gallery as $item) {
                if (! $item) {
                    continue;
                }
                $url = str_starts_with($item, 'http') ? $item : Storage::disk('public')->url($item);
                if (! in_array($url, $urls, true)) {
                    $urls[] = $url;
                }
            }
        }

        return $urls;
    }

    /** Lấy giá trị thông số theo mã (code) – dùng khi hiển thị nhanh. */
    public function spec(string $code): ?string
    {
        $value = $this->attributeValues->first(fn ($v) => $v->attribute?->code === $code);

        return $value?->value;
    }

    /**
     * Chỉ số hoàn thiện dữ liệu (%): tên, mô tả, mô tả ngắn, màu, giá > 0,
     * thương hiệu, năm sản xuất + từng thông số bắt buộc của dòng xe.
     */
    public function calculateCompleteness(): int
    {
        $checks = [
            filled($this->name),
            filled($this->description),
            filled($this->short_description),
            filled($this->color),
            $this->price > 0,
            filled($this->brand_id),
            $this->year >= 1990,
        ];

        $required = Attribute::where('category_id', $this->category_id)
            ->where('is_required', true)->pluck('id');

        $filled = $this->attributeValues()->whereIn('attribute_id', $required)
            ->where('value', '!=', '')->pluck('attribute_id');

        foreach ($required as $id) {
            $checks[] = $filled->contains($id);
        }

        return (int) round(count(array_filter($checks)) / count($checks) * 100);
    }

    /** Danh sách thông số bắt buộc còn thiếu (dùng để chặn xuất bản). */
    public function missingRequiredAttributes(): array
    {
        $required = Attribute::where('category_id', $this->category_id)->where('is_required', true)->get();
        $filled = $this->attributeValues()->where('value', '!=', '')->pluck('attribute_id');

        return $required->reject(fn ($a) => $filled->contains($a->id))->pluck('name')->all();
    }

    public function refreshCompleteness(): void
    {
        $this->forceFill(['completeness' => $this->calculateCompleteness()])->saveQuietly();
    }
}
