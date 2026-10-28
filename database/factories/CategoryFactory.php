<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Coupe', 'Mui trần', 'Minivan', 'Xe tải nhẹ', 'Wagon', 'Limousine']).' '.fake()->unique()->numerify('###');

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->sentence(8),
            'sort_order' => fake()->numberBetween(10, 99),
        ];
    }
}
