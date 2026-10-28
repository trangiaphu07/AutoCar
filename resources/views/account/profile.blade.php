@extends('layouts.app')

@section('title', 'Thông tin cá nhân | AutoShop')

@section('content')
<div class="container">
    <div class="breadcrumb">
        <a href="{{ route('home') }}">Trang chủ</a> /
        <a href="{{ route('account.profile') }}">Tài khoản</a> /
        <span>Thông tin cá nhân</span>
    </div>

    <h1>Tài khoản của tôi</h1>
    @include('account._nav')

    @if(session('success'))
        <div class="alert alert-ok" style="margin-top:20px">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-bad" style="margin-top:20px">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-bad" style="margin-top:20px">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid-2" style="margin-top:20px;align-items:start">
        <!-- Thông tin cơ bản -->
        <div class="card card-body">
            <h2 style="font-size:18px;margin-bottom:6px">Thông tin cá nhân</h2>
            <p class="muted" style="margin-bottom:20px">Quản lý họ tên, số điện thoại và email liên hệ nhận thông báo đơn hàng.</p>

            <form method="post" action="{{ route('account.profile.update') }}">
                @csrf
                @method('put')

                <div class="field">
                    <label for="p-name">Họ và tên <b style="color:var(--bad)">*</b></label>
                    <input class="input" id="p-name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Nguyễn Văn A">
                </div>

                <div class="field">
                    <label for="p-email">Địa chỉ Email <b style="color:var(--bad)">*</b></label>
                    <input class="input" id="p-email" type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="email@example.com">
                </div>

                <div class="field">
                    <label for="p-phone">Số điện thoại</label>
                    <input class="input" id="p-phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0909xxxxxx">
                </div>

                <div class="field">
                    <label>Vai trò</label>
                    <div>
                        @if($user->isAdmin())
                            <span class="badge badge-accent">Quản trị viên</span>
                        @else
                            <span class="badge badge-info">Khách hàng</span>
                        @endif
                        <small class="muted" style="margin-left:8px">Tham gia từ ngày {{ $user->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>

                <button class="btn" type="submit" style="margin-top:10px">Lưu thay đổi</button>
            </form>
        </div>

        <!-- Đổi mật khẩu -->
        <div class="card card-body">
            <h2 style="font-size:18px;margin-bottom:6px">Đổi mật khẩu</h2>
            <p class="muted" style="margin-bottom:20px">Để bảo mật tài khoản, không chia sẻ mật khẩu của bạn cho người khác.</p>

            <form method="post" action="{{ route('account.password.update') }}">
                @csrf
                @method('put')

                <div class="field">
                    <label for="p-cur-pass">Mật khẩu hiện tại <b style="color:var(--bad)">*</b></label>
                    <input class="input" id="p-cur-pass" type="password" name="current_password" required placeholder="Nhập mật khẩu đang dùng">
                </div>

                <div class="field">
                    <label for="p-new-pass">Mật khẩu mới <b style="color:var(--bad)">*</b></label>
                    <input class="input" id="p-new-pass" type="password" name="password" required minlength="6" placeholder="Tối thiểu 6 ký tự">
                    <small class="muted">Chỉ dùng chữ không dấu và ký tự chuẩn.</small>
                </div>

                <div class="field">
                    <label for="p-conf-pass">Xác nhận mật khẩu mới <b style="color:var(--bad)">*</b></label>
                    <input class="input" id="p-conf-pass" type="password" name="password_confirmation" required minlength="6" placeholder="Nhập lại mật khẩu mới">
                </div>

                <button class="btn btn-dark" type="submit" style="margin-top:10px">Cập nhật mật khẩu</button>
            </form>
        </div>
    </div>
</div>
@endsection
