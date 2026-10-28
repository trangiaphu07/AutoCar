<!DOCTYPE html>
<html lang="vi" data-theme="light" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quản trị') – AutoShop Admin</title>
    <script>try{var t=localStorage.getItem('autoshop-theme');if(t){document.documentElement.setAttribute('data-theme',t);document.documentElement.setAttribute('data-bs-theme',t);}}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Bootstrap 5.3 (lưu cục bộ trong public/vendor, chạy được khi không có Internet) --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2">
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="logo"><span class="logo-mark"><x-icon name="car"/></span><span>Auto<em>Shop</em></span></a>
        <div class="sep">Tổng quan</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><x-icon name="chart"/> Dashboard</a>
        <div class="sep">Sản phẩm (PIM)</div>
        <a href="{{ route('admin.cars.index') }}" class="{{ request()->routeIs('admin.cars.*') ? 'active' : '' }}"><x-icon name="car"/> Quản lý xe</a>
        <a href="{{ route('admin.catalog') }}" class="{{ request()->routeIs('admin.catalog*') ? 'active' : '' }}"><x-icon name="tag"/> Dòng xe, hãng, thông số</a>
        <a href="{{ route('admin.io.index') }}" class="{{ request()->routeIs('admin.io.*') ? 'active' : '' }}"><x-icon name="file"/> Nhập / xuất CSV</a>
        <div class="sep">Bán hàng</div>
        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><x-icon name="list"/> Đơn đặt xe</a>
        <a href="{{ route('admin.testdrives.index') }}" class="{{ request()->routeIs('admin.testdrives.*') ? 'active' : '' }}"><x-icon name="calendar"/> Lịch lái thử</a>
        <div class="sep">Hệ thống</div>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><x-icon name="user"/> Người dùng</a>
        <div class="sep">Khác</div>
        <a href="{{ route('home') }}" target="_blank"><x-icon name="home"/> Xem cửa hàng</a>
    </aside>
    <div class="admin-main">
        <div class="admin-top">
            <b>Không gian quản trị</b>
            <div class="who">
                <button class="icon-btn" type="button" data-theme-toggle><x-icon name="moon"/><span class="hide-sm" data-theme-label>Tối</span></button>
                <span class="hide-sm">{{ auth()->user()->name }}</span>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-ghost btn-sm" type="submit"><x-icon name="logout"/> Đăng xuất</button></form>
            </div>
        </div>
        <div class="admin-content">
            {{-- Flash message: Alert có nút đóng của Bootstrap 5 --}}
            @if(session('success'))<div class="alert alert-ok alert-dismissible fade show" role="status">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button></div>@endif
            @if(session('error'))<div class="alert alert-bad alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button></div>@endif
            @yield('content')
        </div>
    </div>
</div>
@include('components.confirm-modal')
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}?v=2"></script>
</body>
</html>
