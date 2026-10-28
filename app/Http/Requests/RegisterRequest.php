<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** Đăng ký tài khoản khách hàng. */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            // Mật khẩu chỉ gồm ký tự ASCII in được (không dấu tiếng Việt)
            'password' => ['required', 'confirmed', 'regex:/^[\x21-\x7E]+$/', Password::min(8)->letters()->numbers()],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'họ tên', 'email' => 'email', 'phone' => 'số điện thoại', 'password' => 'mật khẩu'];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Mật khẩu chỉ được chứa chữ không dấu, số và ký tự đặc biệt (không dùng dấu tiếng Việt, khoảng trắng).',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'email.unique' => 'Email này đã được đăng ký.',
        ];
    }
}
