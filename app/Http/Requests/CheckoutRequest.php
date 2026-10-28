<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Thông tin người đứng tên khi đặt mua / đặt cọc xe. */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // khách vãng lai cũng được đặt xe
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'customer_id_number' => ['nullable', 'digits:12'],
            'address' => ['required', 'string', 'max:255'],
            'showroom' => ['required', Rule::in(config('autoshop.showrooms'))],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
            'note' => ['nullable', 'string', 'max:1000'],
            'agree' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'họ tên', 'customer_phone' => 'số điện thoại', 'customer_email' => 'email',
            'customer_id_number' => 'số CCCD', 'address' => 'địa chỉ', 'showroom' => 'showroom nhận xe',
            'payment_method' => 'hình thức đặt cọc', 'note' => 'ghi chú',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Số điện thoại không hợp lệ (ví dụ 0909123456).',
            'customer_id_number.digits' => 'Số CCCD phải gồm đúng 12 chữ số.',
            'agree.accepted' => 'Bạn cần đồng ý với điều khoản đặt cọc.',
        ];
    }
}
