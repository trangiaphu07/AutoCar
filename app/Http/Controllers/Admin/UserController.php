<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::withCount(['orders', 'testDrives'])->latest('id');

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($q = trim($request->input('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $users = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'customers' => User::where('role', 'customer')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create(): View
    {
        $user = new User(['role' => 'customer']);

        return view('admin.users.form', compact('user'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Đã thêm tài khoản mới thành công!');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->id === auth()->id() && $data['role'] !== 'admin') {
            return back()->with('error', 'Bạn không thể tự hạ quyền quản trị của chính mình.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Đã cập nhật tài khoản thành công!');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể xóa tài khoản của chính mình.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Hệ thống cần ít nhất 1 tài khoản quản trị.');
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', 'Đã xóa tài khoản "'.$userName.'" thành công.');
    }
}
