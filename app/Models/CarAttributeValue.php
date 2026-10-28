<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarAttributeValue extends Model
{
    protected $fillable = ['car_id', 'attribute_id', 'value'];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
