@extends('layouts.app')

@section('title', 'Đăng ký – AutoShop')

@section('content')
<div class="container">
    <div class="card card-body auth">
        <h1>Tạo tài khoản</h1>
        @if($errors->any())<div class="alert alert-bad"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="post" action="{{ route('register') }}">
            @csrf
            <div class="field"><label for="name">Họ và tên</label><input class="input" id="name" name="name" value="{{ old('name') }}" required></div>
            <div class="field"><label for="email">Email</label><input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required></div>
            <div class="field"><label for="phone">Số điện thoại</label><input class="input" id="phone" name="phone" value="{{ old('phone') }}"></div>
            <div class="field"><label for="password">Mật khẩu</label>
                <div class="pw-wrap"><input class="input" id="password" type="password" name="password" required minlength="8"><button class="pw-toggle" type="button" data-pw-toggle>Hiện</button></div>
                <small>Tối thiểu 8 ký tự, có chữ và số, không dùng dấu tiếng Việt.</small>
            </div>
            <div class="field"><label for="password_confirmation">Nhập lại mật khẩu</label>
                <div class="pw-wrap"><input class="input" id="password_confirmation" type="password" name="password_confirmation" required><button class="pw-toggle" type="button" data-pw-toggle>Hiện</button></div>
            </div>
            <button class="btn btn-block" type="submit">Đăng ký</button>
        </form>
        <p style="margin:16px 0 0;text-align:center">Đã có tài khoản? <a href="{{ route('login') }}" style="color:var(--accent);font-weight:600">Đăng nhập</a></p>
    </div>
</div>
@endsection
