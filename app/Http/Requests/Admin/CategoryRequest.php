<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Thêm / sửa dòng xe (bảng categories). */
class CategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($this->route('category'))],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'tên dòng xe', 'description' => 'mô tả', 'sort_order' => 'thứ tự hiển thị'];
    }

    public function messages(): array
    {
        return ['name.unique' => 'Dòng xe “:input” đã tồn tại.'];
    }
}
