<?php

namespace App\Http\Requests\Admin;

use App\Models\Attribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Thêm / sửa định nghĩa thông số kỹ thuật động (bảng attributes – mô hình EAV). */
class AttributeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        // Mã tự sinh từ tên nếu bỏ trống
        if (blank($this->input('code')) && filled($this->input('name'))) {
            $this->merge(['code' => Str::slug($this->input('name'), '_')]);
        }
    }

    public function rules(): array
    {
        $attribute = $this->route('attribute');
        $categoryId = $attribute?->category_id ?? $this->input('category_id');

        return [
            'category_id' => [$attribute ? 'nullable' : 'required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('attributes', 'code')->where('category_id', $categoryId)->ignore($attribute)],
            'type' => ['required', Rule::in(array_keys(Attribute::TYPES))],
            'unit' => ['nullable', 'string', 'max:20'],
            'options' => ['nullable', 'string', 'required_if:type,select'],
            'is_required' => ['nullable', 'boolean'],
            'is_filterable' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['category_id' => 'dòng xe', 'name' => 'tên thông số', 'code' => 'mã thông số', 'type' => 'kiểu dữ liệu', 'unit' => 'đơn vị'];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Mã chỉ gồm chữ thường không dấu, số và dấu gạch dưới.',
            'code.unique' => 'Mã thông số đã tồn tại trong dòng xe này.',
            'options.required_if' => 'Kiểu lựa chọn cần nhập danh sách giá trị (cách nhau bằng dấu phẩy).',
        ];
    }

    /** Dữ liệu đã chuẩn hóa để lưu vào bảng attributes. */
    public function payload(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['name'],
            'code' => $data['code'],
            'type' => $data['type'],
            'unit' => $data['unit'] ?? null,
            'options' => $data['type'] === 'select'
                ? collect(explode(',', (string) $data['options']))->map(fn ($o) => trim($o))->filter()->values()->all()
                : null,
            'is_required' => $this->boolean('is_required'),
            'is_filterable' => $this->boolean('is_filterable'),
        ];
    }
}
