<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarRequest;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $query = Car::with(['brand', 'category'])->latest('id');

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($cat = $request->query('category')) {
            $query->where('category_id', $cat);
        }

        return view('admin.cars.index', [
            'cars' => $query->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $car = new Car(['status' => 'draft', 'condition' => 'new', 'year' => (int) date('Y'), 'color_hex' => '#1f6feb', 'stock' => 1]);
        $car->category_id = (int) $request->query('category', Category::orderBy('sort_order')->value('id'));

        return view('admin.cars.form', $this->formData($car));
    }

    public function store(CarRequest $request)
    {
        $car = new Car;
        $this->save($request, $car);

        return redirect()->route('admin.cars.edit', $car)->with('success', "Đã thêm xe “{$car->name}”.");
    }

    public function edit(Car $car)
    {
        $car->load('attributeValues');

        return view('admin.cars.form', $this->formData($car));
    }

    public function update(CarRequest $request, Car $car)
    {
        $this->save($request, $car);

        return redirect()->route('admin.cars.edit', $car)->with('success', 'Đã lưu thay đổi.');
    }

    public function destroy(Car $car)
    {
        $car->delete(); // xóa mềm – giữ lịch sử đơn hàng

        return redirect()->route('admin.cars.index')->with('success', "Đã xóa xe “{$car->name}”.");
    }

    /** Trả về HTML các ô thông số theo dòng xe (dùng khi đổi dòng xe trong form). */
    public function attributeFields(Category $category, Request $request)
    {
        $car = $request->query('car') ? Car::with('attributeValues')->find($request->query('car')) : null;

        return view('admin.cars._attributes', [
            'attributes' => $category->attributes,
            'values' => $car && $car->category_id === $category->id ? $car->attributeValues->pluck('value', 'attribute_id') : collect(),
        ]);
    }

    private function formData(Car $car): array
    {
        $category = Category::with('attributes')->find(old('category_id', $car->category_id));

        return [
            'car' => $car,
            'categories' => Category::orderBy('sort_order')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'attributes' => $category?->attributes ?? collect(),
            'values' => $car->exists ? $car->attributeValues->pluck('value', 'attribute_id') : collect(),
        ];
    }

    /**
     * Lưu xe + thông số động. Dữ liệu đã được kiểm tra trong CarRequest
     * (bao gồm kiểm tra kiểu thông số và chặn xuất bản khi thiếu thông số bắt buộc).
     */
    private function save(CarRequest $request, Car $car): void
    {
        $data = $request->validated();
        $attributes = $request->categoryAttributes();
        $attrInput = $request->input('attr', []);

        DB::transaction(function () use ($request, $car, $data, $attributes, $attrInput) {
            $slugBase = Str::slug($data['name'].'-'.$data['year']);
            $slug = $slugBase;
            $n = 2;
            while (Car::withTrashed()->where('slug', $slug)->where('id', '!=', $car->id ?? 0)->exists()) {
                $slug = $slugBase.'-'.$n++;
            }

            $fill = collect($data)->except(['image', 'image_url', 'gallery_text', 'remove_image', 'attr'])->all();
            $fill['slug'] = $slug;
            $fill['is_featured'] = $request->boolean('is_featured');
            $fill['mileage'] = (int) ($data['mileage'] ?? 0);
            $fill['color_hex'] = $data['color_hex'] ?? '#1f6feb';

            if ($request->boolean('remove_image')) {
                if ($car->image && ! str_starts_with($car->image, 'http')) {
                    Storage::disk('public')->delete($car->image);
                }
                $fill['image'] = null;
                $fill['gallery'] = null;
            } else {
                if ($request->hasFile('image')) {
                    $fill['image'] = $request->file('image')->store('cars', 'public');
                } elseif ($request->filled('image_url')) {
                    $fill['image'] = trim($request->input('image_url'));
                }

                if ($request->has('gallery_text')) {
                    $lines = array_values(array_filter(array_map('trim', explode("\n", (string) $request->input('gallery_text')))));
                    $fill['gallery'] = count($lines) ? $lines : null;
                }
            }

            $car->fill($fill)->save();

            // Đổi dòng xe -> xóa thông số không còn thuộc dòng mới
            $car->attributeValues()->whereNotIn('attribute_id', $attributes->pluck('id'))->delete();

            foreach ($attributes as $a) {
                $v = trim((string) ($attrInput[$a->id] ?? ''));
                if ($v === '') {
                    $car->attributeValues()->where('attribute_id', $a->id)->delete();
                } else {
                    $car->attributeValues()->updateOrCreate(['attribute_id' => $a->id], ['value' => $v]);
                }
            }

            $car->refreshCompleteness();
        });
    }
}
