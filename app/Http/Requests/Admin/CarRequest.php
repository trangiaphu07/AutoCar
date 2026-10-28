<?php

namespace App\Http\Requests\Admin;

use App\Models\Attribute;
use App\Models\Car;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Kiểm tra dữ liệu thêm/sửa xe (PIM).
 * Ngoài các trường cố định, còn kiểm tra thông số động (EAV) theo dòng xe
 * và chặn xuất bản khi còn thiếu thông số bắt buộc.
 */
class CarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $carId = $this->route('car')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['required', 'string', 'max:40', Rule::unique('cars', 'sku')->ignore($carId)],
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'condition' => ['required', Rule::in(array_keys(Car::CONDITIONS))],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'max:40'],
            'color_hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'price' => ['required', 'integer', 'min:1'],
            'sale_price' => ['nullable', 'integer', 'min:1', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:999'],
            'status' => ['required', Rule::in(array_keys(Car::STATUSES))],
            'is_featured' => ['nullable', 'boolean'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'gallery_text' => ['nullable', 'string'],
            'remove_image' => ['nullable', 'boolean'],
            'attr' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá niêm yết.',
            'sku.unique' => 'Mã xe (SKU) này đã tồn tại.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'dòng xe', 'brand_id' => 'hãng xe', 'sku' => 'mã xe (SKU)', 'name' => 'tên xe',
            'year' => 'năm sản xuất', 'condition' => 'tình trạng', 'mileage' => 'số km đã đi', 'color' => 'màu xe',
            'price' => 'giá niêm yết', 'sale_price' => 'giá khuyến mãi', 'stock' => 'số lượng tồn kho',
            'status' => 'trạng thái', 'image' => 'ảnh đại diện',
        ];
    }

    /** Kiểm tra thông số động theo kiểu dữ liệu và chặn xuất bản khi thiếu thông số bắt buộc. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('category_id')) {
                    return;
                }
                $input = (array) $this->input('attr', []);

                foreach ($this->categoryAttributes() as $a) {
                    $v = trim((string) ($input[$a->id] ?? ''));
                    if ($v === '') {
                        if ($a->is_required && $this->input('status') === 'published') {
                            $validator->errors()->add("attr.{$a->id}", "Thiếu thông số bắt buộc “{$a->name}” để xuất bản.");
                        }

                        continue;
                    }
                    if ($a->type === 'number' && ! is_numeric($v)) {
                        $validator->errors()->add("attr.{$a->id}", "“{$a->name}” phải là số.");
                    }
                    if ($a->type === 'select' && ! in_array($v, $a->options ?? [], true)) {
                        $validator->errors()->add("attr.{$a->id}", "Giá trị “{$a->name}” không hợp lệ.");
                    }
                }
            },
        ];
    }

    /** Danh sách thông số (EAV) của dòng xe đang chọn. */
    public function categoryAttributes()
    {
        return Attribute::where('category_id', $this->input('category_id'))->orderBy('sort_order')->orderBy('id')->get();
    }
}
