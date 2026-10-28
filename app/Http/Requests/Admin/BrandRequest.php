<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Thêm / sửa hãng xe (bảng brands). */
class BrandRequest extends FormRequest
{

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('brands', 'name')->ignore($this->route('brand'))],
            'country' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'tên hãng xe', 'country' => 'quốc gia'];
    }

    public function messages(): array
    {
        return ['name.unique' => 'Hãng xe “:input” đã tồn tại.'];
    }
}
