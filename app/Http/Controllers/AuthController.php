<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.']);
        }

        $request->session()->regenerate();

        $default = Auth::user()->isAdmin() ? route('admin.dashboard') : route('home');

        return redirect()->intended($default)->with('success', 'Xin chào, '.Auth::user()->name.'!');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data + ['role' => 'customer']);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Tạo tài khoản thành công. Chào mừng bạn đến AutoShop!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Đã đăng xuất.');
    }
}
