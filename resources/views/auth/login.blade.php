@extends('layouts.app')

@section('title', 'Đăng nhập – AutoShop')

@section('content')
<div class="container">
    <div class="card card-body auth">
        <h1>Đăng nhập</h1>
        <p class="muted">Theo dõi đơn đặt xe, lịch lái thử và xe yêu thích.</p>
        @if($errors->any())<div class="alert alert-bad">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('login') }}">
            @csrf
            <div class="field"><label for="email">Email</label><input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus></div>
            <div class="field"><label for="password">Mật khẩu</label>
                <div class="pw-wrap"><input class="input" id="password" type="password" name="password" required><button class="pw-toggle" type="button" data-pw-toggle>Hiện</button></div>
            </div>
            <label class="check" style="margin-bottom:14px"><input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập</label>
            <button class="btn btn-block" type="submit">Đăng nhập</button>
        </form>
        <p style="margin:16px 0 0;text-align:center">Chưa có tài khoản? <a href="{{ route('register') }}" style="color:var(--accent);font-weight:600">Đăng ký</a></p>
        <div class="alert alert-info" style="margin-bottom:0"><b>Tài khoản demo</b><br>Admin: admin@autoshop.test / password<br>Khách: khach@autoshop.test / password</div>
    </div>
</div>
@endsection
