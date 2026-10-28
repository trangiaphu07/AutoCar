<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'sort_order'];

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class)->orderBy('sort_order')->orderBy('id');
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }
}
