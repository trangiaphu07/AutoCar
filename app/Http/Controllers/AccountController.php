<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class AccountController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();

        return view('account.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        return back()->with('success', 'Thông tin cá nhân đã được cập nhật thành công!');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'regex:/^[\x20-\x7E]+$/'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.regex' => 'Mật khẩu chỉ dùng chữ không dấu và ký tự chuẩn.',
        ]);

        $user->update([
            'password' => $request->password,
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    public function orders(Request $request)
    {
        $orders = $request->user()->orders()->with('items')->latest('id')->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->canBeCancelledByCustomer()) {
            return back()->with('error', 'Đơn đã đặt cọc hoặc đã được xử lý, vui lòng liên hệ hotline để hủy.');
        }

        try {
            $order->transitionTo('cancelled');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Đã hủy đơn {$order->code}.");
    }

    public function testDrives(Request $request)
    {
        $drives = $request->user()->testDrives()->with('car')->latest('id')->get();

        return view('account.testdrives', compact('drives'));
    }

    public function wishlist(Request $request)
    {
        $cars = $request->user()->wishlist()->with(['brand', 'category', 'attributeValues.attribute'])->published()->get();

        return view('account.wishlist', compact('cars'));
    }

    public function toggleWishlist(Request $request, Car $car)
    {
        $result = $request->user()->wishlist()->toggle($car->id);
        $added = ! empty($result['attached']);

        return back()->with('success', $added ? "Đã lưu “{$car->name}” vào danh sách yêu thích." : "Đã bỏ “{$car->name}” khỏi danh sách yêu thích.");
    }
}
