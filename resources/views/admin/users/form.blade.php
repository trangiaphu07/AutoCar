@extends('layouts.admin')

@section('title', $user->exists ? 'Sửa tài khoản: '.$user->name : 'Thêm tài khoản mới')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $user->exists ? 'Sửa tài khoản' : 'Thêm tài khoản mới' }}</h1>
        <p class="muted">{{ $user->exists ? 'Cập nhật quyền và thông tin người dùng' : 'Tạo mới tài khoản người dùng hoặc quản trị viên' }}</p>
    </div>
    <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">← Quay lại danh sách</a>
</div>

@if($errors->any())
    <div class="alert alert-bad" style="margin-bottom:20px">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" style="max-width:700px">
    @csrf
    @if($user->exists)
        @method('put')
    @endif

    <div class="card card-body">
        <div class="field">
            <label for="u-name">Họ và tên <b style="color:var(--bad)">*</b></label>
            <input class="input" id="u-name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Ví dụ: Nguyễn Văn A">
        </div>

        <div class="grid-2">
            <div class="field">
                <label for="u-email">Địa chỉ Email <b style="color:var(--bad)">*</b></label>
                <input class="input" id="u-email" type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="email@example.com">
            </div>
            <div class="field">
                <label for="u-phone">Số điện thoại</label>
                <input class="input" id="u-phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0909xxxxxx">
            </div>
        </div>

        <div class="field">
            <label for="u-role">Vai trò tài khoản <b style="color:var(--bad)">*</b></label>
            <select class="input" id="u-role" name="role" required>
                <option value="customer" @selected(old('role', $user->role) === 'customer')>Khách hàng (Xem xe, đặt cọc, đăng ký lái thử)</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Quản trị viên (Toàn quyền quản trị hệ thống)</option>
            </select>
        </div>

        <hr style="border:none;border-top:1px solid var(--line);margin:20px 0">

        <div class="field">
            <label for="u-pass">
                {{ $user->exists ? 'Đổi mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu' }}
                @if(! $user->exists)<b style="color:var(--bad)">*</b>@endif
            </label>
            <input class="input" id="u-pass" type="password" name="password" {{ $user->exists ? '' : 'required' }} minlength="6" placeholder="Tối thiểu 6 ký tự chuẩn">
            <small class="muted">Chỉ dùng chữ cái không dấu, số và ký tự đặc biệt tiêu chuẩn.</small>
        </div>

        <div style="margin-top:24px;display:flex;gap:12px">
            <button class="btn" type="submit">{{ $user->exists ? 'Cập nhật tài khoản' : 'Tạo tài khoản' }}</button>
            <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Hủy</a>
        </div>
    </div>
</form>
@endsection
