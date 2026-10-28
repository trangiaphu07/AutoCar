<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử CRUD đầy đủ trên các bảng có quan hệ: dòng xe (categories) 1–n xe (cars),
 * hãng xe (brands) 1–n xe, dòng xe 1–n thông số (attributes); kèm validation và phân quyền.
 */
class CatalogCrudTest extends TestCase
{
    use RefreshDatabase;

    /** Dùng chung CSDL đã seed với ShopFlowTest (RefreshDatabase chỉ migrate một lần mỗi lượt chạy). */
    protected bool $seed = true;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_factories_create_related_records(): void
    {
        $car = Car::factory()->published()->create();

        $this->assertInstanceOf(Category::class, $car->category);
        $this->assertInstanceOf(Brand::class, $car->brand);
        $this->assertTrue($car->category->cars->contains($car));   // hasMany
        $this->assertTrue($car->brand->cars->contains($car));      // hasMany
    }

    public function test_admin_can_create_update_delete_category(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/catalog/categories', ['name' => 'MPV', 'description' => 'Xe đa dụng'])
            ->assertSessionHas('success');
        $category = Category::where('name', 'MPV')->firstOrFail();

        $this->get('/admin/catalog')->assertOk()->assertSee('MPV');

        $this->put("/admin/catalog/categories/{$category->id}", ['name' => 'MPV 7 chỗ', 'description' => 'Đã sửa', 'sort_order' => 3])
            ->assertSessionHas('success');
        $this->assertSame('MPV 7 chỗ', $category->fresh()->name);
        $this->assertSame('mpv-7-cho', $category->fresh()->slug);

        $this->delete("/admin/catalog/categories/{$category->id}")->assertSessionHas('success');
        $this->assertModelMissing($category);
    }

    public function test_category_with_cars_cannot_be_deleted(): void
    {
        $car = Car::factory()->create();

        $this->actingAs($this->admin())->delete("/admin/catalog/categories/{$car->category_id}")
            ->assertSessionHas('error');
        $this->assertModelExists($car->category);
    }

    public function test_category_validation_messages(): void
    {
        // "Sedan" đã có sẵn trong dữ liệu mẫu
        $this->actingAs($this->admin())->from('/admin/catalog')
            ->post('/admin/catalog/categories', ['name' => 'Sedan'])
            ->assertRedirect('/admin/catalog')
            ->assertSessionHasErrors(['name' => 'Dòng xe “Sedan” đã tồn tại.']);

        $this->post('/admin/catalog/categories', ['name' => ''])->assertSessionHasErrors('name');
    }

    public function test_admin_can_create_update_delete_brand(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/catalog/brands', ['name' => 'Peugeot', 'country' => 'Pháp'])->assertSessionHas('success');
        $brand = Brand::where('name', 'Peugeot')->firstOrFail();

        $this->put("/admin/catalog/brands/{$brand->id}", ['name' => 'Peugeot (Stellantis)', 'country' => 'Pháp'])->assertSessionHas('success');
        $this->assertSame('Peugeot (Stellantis)', $brand->fresh()->name);

        $car = Car::factory()->create(['brand_id' => $brand->id]);
        $this->delete("/admin/catalog/brands/{$brand->id}")->assertSessionHas('success');
        $this->assertModelMissing($brand);
        $this->assertNull($car->fresh()->brand_id); // khóa ngoại nullOnDelete
    }

    public function test_admin_can_update_attribute_definition(): void
    {
        $category = Category::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/catalog/attributes', [
            'category_id' => $category->id, 'name' => 'Số túi khí', 'type' => 'number', 'unit' => 'túi',
        ])->assertSessionHas('success');
        $attr = Attribute::where('code', 'so_tui_khi')->firstOrFail();

        $this->put("/admin/catalog/attributes/{$attr->id}", [
            'name' => 'Hệ thống an toàn', 'code' => 'an_toan', 'type' => 'select', 'options' => 'Cơ bản, Nâng cao',
            'is_required' => '1', 'is_filterable' => '1',
        ])->assertSessionHas('success');

        $attr->refresh();
        $this->assertSame('an_toan', $attr->code);
        $this->assertSame(['Cơ bản', 'Nâng cao'], $attr->options);
        $this->assertTrue($attr->is_required);

        // Kiểu "Lựa chọn" bắt buộc có danh sách giá trị
        $this->put("/admin/catalog/attributes/{$attr->id}", ['name' => 'X', 'code' => 'an_toan', 'type' => 'select'])
            ->assertSessionHasErrors('options');
    }

    public function test_customer_cannot_manage_catalog(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();

        $this->actingAs($customer)->put("/admin/catalog/categories/{$category->id}", ['name' => 'Hack'])->assertForbidden();
        $this->post('/admin/catalog/brands', ['name' => 'Hack'])->assertForbidden();
        $this->assertNotSame('Hack', $category->fresh()->name);
    }

    public function test_admin_car_search_keeps_keyword_when_paginating(): void
    {
        Car::factory()->count(20)->for(Category::factory())->for(Brand::factory())->create(['name' => 'Xe Thử Nghiệm']);

        $this->actingAs($this->admin())->get('/admin/cars?q=Thử+Nghiệm')
            ->assertOk()
            ->assertSee('q=Th%E1%BB%AD%20Nghi%E1%BB%87m&amp;page=2', false);
    }
}
