<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\TestDrive;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TestDriveController extends Controller
{
    public function store(Request $request, Car $car)
    {
        abort_unless($car->status === 'published', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'email' => ['nullable', 'email'],
            'showroom' => ['required', Rule::in(config('autoshop.showrooms'))],
            'preferred_date' => ['required', 'date', 'after_or_equal:today', 'before:+60 days'],
            'preferred_time' => ['required', Rule::in(['08:30', '09:30', '10:30', '13:30', '14:30', '15:30', '16:30'])],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'Số điện thoại không hợp lệ (ví dụ 0909123456).',
            'preferred_date.after_or_equal' => 'Ngày lái thử phải từ hôm nay trở đi.',
            'preferred_date.before' => 'Chỉ nhận đăng ký trong vòng 60 ngày tới.',
        ]);

        $duplicate = TestDrive::where('car_id', $car->id)->where('phone', $data['phone'])
            ->whereDate('preferred_date', $data['preferred_date'])->whereIn('status', ['new', 'confirmed'])->exists();
        if ($duplicate) {
            return back()->withInput()->with('error', 'Bạn đã đăng ký lái thử xe này vào ngày đã chọn.');
        }

        TestDrive::create($data + ['car_id' => $car->id, 'user_id' => auth()->id(), 'status' => 'new']);

        return back()->with('success', 'Đăng ký lái thử thành công! AutoShop sẽ gọi xác nhận lịch cho bạn.');
    }
}
