<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributeRequest;
use App\Http\Requests\Admin\BrandRequest;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Support\Str;

/**
 * Quản lý danh mục nghiệp vụ: dòng xe (categories), hãng xe (brands) và
 * định nghĩa thông số kỹ thuật động (attributes). Mỗi bảng đều có đủ
 * Thêm – Xem – Sửa – Xóa; form sửa hiển thị trong Bootstrap Modal.
 */
class CatalogController extends Controller
{
    public function index()
    {
        return view('admin.catalog.index', [
            'categories' => Category::with('attributes')->withCount('cars')->orderBy('sort_order')->get(),
            'brands' => Brand::withCount('cars')->orderBy('name')->get(),
        ]);
    }

    /* ---------------- Dòng xe ---------------- */

    public function storeCategory(CategoryRequest $request)
    {
        $data = $request->validated();
        Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'slug' => $this->uniqueSlug(Category::class, $data['name']),
            'sort_order' => $data['sort_order'] ?? Category::max('sort_order') + 1,
        ]);

        return back()->with('success', "Đã thêm dòng xe “{$data['name']}”.");
    }

    public function updateCategory(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'slug' => $this->uniqueSlug(Category::class, $data['name'], $category->id),
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ]);

        return back()->with('success', "Đã cập nhật dòng xe “{$category->name}”.");
    }

    public function destroyCategory(Category $category)
    {
        if (Car::withTrashed()->where('category_id', $category->id)->exists()) {
            return back()->with('error', 'Không thể xóa dòng xe đang có xe.');
        }
        $category->delete();

        return back()->with('success', 'Đã xóa dòng xe.');
    }

    /* ---------------- Hãng xe ---------------- */

    public function storeBrand(BrandRequest $request)
    {
        $data = $request->validated();
        Brand::create($data + ['slug' => $this->uniqueSlug(Brand::class, $data['name'])]);

        return back()->with('success', "Đã thêm hãng xe “{$data['name']}”.");
    }

    public function updateBrand(BrandRequest $request, Brand $brand)
    {
        $data = $request->validated();
        $brand->update($data + ['slug' => $this->uniqueSlug(Brand::class, $data['name'], $brand->id)]);

        return back()->with('success', "Đã cập nhật hãng xe “{$brand->name}”.");
    }

    public function destroyBrand(Brand $brand)
    {
        $brand->delete(); // cars.brand_id -> null (nullOnDelete)

        return back()->with('success', 'Đã xóa hãng xe.');
    }

    /* ---------------- Thông số kỹ thuật (EAV) ---------------- */

    public function storeAttribute(AttributeRequest $request)
    {
        $categoryId = (int) $request->validated('category_id');

        Attribute::create($request->payload() + [
            'category_id' => $categoryId,
            'sort_order' => Attribute::where('category_id', $categoryId)->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Đã thêm thông số. Không cần sửa mã nguồn hay migration!');
    }

    public function updateAttribute(AttributeRequest $request, Attribute $attribute)
    {
        $attribute->update($request->payload());

        // Cờ "bắt buộc" có thể thay đổi -> tính lại % hoàn thiện của các xe thuộc dòng này
        Car::where('category_id', $attribute->category_id)->get()->each->refreshCompleteness();

        return back()->with('success', "Đã cập nhật thông số “{$attribute->name}”.");
    }

    public function destroyAttribute(Attribute $attribute)
    {
        $attribute->delete();

        return back()->with('success', 'Đã xóa thông số.');
    }

    /** Sinh slug không trùng cho bảng categories / brands. */
    private function uniqueSlug(string $model, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'muc';
        $slug = $base;
        $n = 2;
        while ($model::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }
}
