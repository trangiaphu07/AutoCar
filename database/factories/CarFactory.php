<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Sinh xe mẫu ngẫu nhiên (dùng trong kiểm thử hoặc để tạo nhiều dữ liệu thử phân trang).
 * Ví dụ: Car::factory()->count(30)->published()->create();
 *
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Xe mẫu '.fake()->unique()->bothify('??-###');
        $year = fake()->numberBetween(2019, (int) date('Y'));
        $price = fake()->numberBetween(30, 250) * 10_000_000;

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'sku' => 'FX'.fake()->unique()->numerify('#####'),
            'name' => $name,
            'slug' => Str::slug($name.'-'.$year),
            'year' => $year,
            'condition' => fake()->randomElement(['new', 'used']),
            'mileage' => 0,
            'color' => fake()->randomElement(['Trắng', 'Đen', 'Đỏ', 'Xám', 'Xanh']),
            'color_hex' => fake()->hexColor(),
            'price' => $price,
            'sale_price' => null,
            'stock' => fake()->numberBetween(1, 10),
            'status' => 'draft',
            'short_description' => fake()->sentence(10),
            'description' => fake()->paragraph(),
        ];
    }

    /** Trạng thái Đang bán. */
    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published']);
    }

    /** Hết hàng. */
    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
