<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Thêm / sửa tài khoản người dùng trong khu vực quản trị. */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'regex:/^[\x21-\x7E]+$/'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'họ tên', 'email' => 'email', 'phone' => 'số điện thoại', 'role' => 'vai trò', 'password' => 'mật khẩu'];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Mật khẩu chỉ dùng chữ không dấu, số và ký tự đặc biệt (không dấu cách).',
            'phone.regex' => 'Số điện thoại không hợp lệ (ví dụ 0909123456).',
            'email.unique' => 'Email này đã được sử dụng.',
        ];
    }
}
