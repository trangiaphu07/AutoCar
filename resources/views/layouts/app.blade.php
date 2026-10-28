<!DOCTYPE html>
<html lang="vi" data-theme="dark" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AutoShop – Showroom ô tô trực tuyến')</title>
    <meta name="description" content="@yield('description', 'AutoShop – mua ô tô mới và đã qua sử dụng, đặt cọc online, đăng ký lái thử, tính trả góp.')">
    <script>try{var t=localStorage.getItem('autoshop-theme');if(t){document.documentElement.setAttribute('data-theme',t);document.documentElement.setAttribute('data-bs-theme',t);}}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Bootstrap 5.3 (lưu cục bộ trong public/vendor, chạy được khi không có Internet) --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=4">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><rect width='24' height='24' rx='6' fill='%23c5a572'/><path d='M5 15h14M5 15l1.4-4a2 2 0 0 1 1.9-1.4h7.4a2 2 0 0 1 1.9 1.4L19 15v3H5z' fill='%2307090d'/></svg>">
</head>
<body class="shop">
<header class="header">
    <div class="container header-row">
        <a href="{{ route('home') }}" class="logo" aria-label="AutoShop – trang chủ">
            <span class="logo-mark"><x-icon name="car"/></span>
            <span>Auto<em>Shop</em></span>
        </a>

        <nav class="main-nav" id="main-nav" aria-label="Điều hướng chính">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Trang chủ</a>
            <a href="{{ route('cars.index') }}" class="{{ request()->routeIs('cars.index') && ! request('condition') ? 'active' : '' }}">Khám phá</a>
            <div class="dropdown nav-drop" data-dropdown>
                <button type="button" class="{{ request()->routeIs('cars.category') || request('condition') === 'used' ? 'active' : '' }}" data-dropdown-btn>Dòng xe <span aria-hidden="true">▾</span></button>
                <div class="dropdown-menu">
                    @foreach($navCategories as $c)
                        <a href="{{ route('cars.category', $c) }}">{{ $c->name }}</a>
                    @endforeach
                    <a href="{{ route('cars.index', ['condition' => 'used']) }}">Xe đã qua sử dụng</a>
                </div>
            </div>
            <a href="{{ route('cars.compare') }}" class="{{ request()->routeIs('cars.compare') ? 'active' : '' }}">So sánh @if($compareCount)<span class="count-inline">{{ $compareCount }}</span>@endif</a>
            <a href="#showroom">Showroom</a>
        </nav>

        <div class="header-actions">
            <button class="icon-btn" type="button" data-search-toggle title="Tìm kiếm xe" aria-controls="search-panel" aria-expanded="false"><x-icon name="search"/></button>
            <button class="icon-btn" type="button" data-theme-toggle title="Đổi giao diện sáng/tối">
                <x-icon name="moon"/><span class="visually-hidden" data-theme-label>Sáng</span>
            </button>
            @auth
                <div class="dropdown" data-dropdown>
                    <button class="icon-btn" type="button" data-dropdown-btn title="Tài khoản"><x-icon name="user"/><span class="hide-md">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 14) }}</span></button>
                    <div class="dropdown-menu">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}">Trang quản trị</a>
                        @endif
                        <a href="{{ route('account.profile') }}">Thông tin cá nhân</a>
                        <a href="{{ route('account.orders') }}">Đơn đặt xe của tôi</a>
                        <a href="{{ route('account.testdrives') }}">Lịch lái thử</a>
                        <a href="{{ route('account.wishlist') }}">Xe yêu thích</a>
                        <form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Đăng xuất</button></form>
                    </div>
                </div>
            @else
                <a class="icon-btn" href="{{ route('login') }}" title="Đăng nhập"><x-icon name="user"/><span class="hide-md">Đăng nhập</span></a>
            @endauth
            <a class="icon-btn" href="{{ route('cart.index') }}" title="Giỏ xe">
                <x-icon name="cart"/>
                @if($cartCount)<span class="count">{{ $cartCount }}</span>@endif
            </a>
            <button class="icon-btn nav-toggle" type="button" data-nav-toggle aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu">
                <span class="burger" aria-hidden="true"><i></i><i></i><i></i></span>
            </button>
        </div>
    </div>

    <div class="search-panel" id="search-panel" hidden>
        <form class="container search" action="{{ route('cars.index') }}" method="get" role="search">
            <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Tìm mẫu xe, hãng xe, mã xe…" aria-label="Tìm kiếm xe">
            <button class="btn" type="submit"><x-icon name="search"/> Tìm</button>
        </form>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="footer" id="showroom">
    <div class="container">
        <div>
            <a href="{{ route('home') }}" class="logo"><span class="logo-mark"><x-icon name="car"/></span><span>Auto<em>Shop</em></span></a>
            <p style="margin-top:14px">Showroom ô tô trực tuyến: xe mới chính hãng và xe đã qua sử dụng được kiểm định. Đặt cọc online, lái thử miễn phí, hỗ trợ trả góp đến 80%.</p>
            <span class="gold-rule"></span>
        </div>
        <div>
            <h4>Hệ thống showroom</h4>
            @foreach(config('autoshop.showrooms') as $s)
                <div class="footer-place">
                    <b>{{ \Illuminate\Support\Str::before($s, ' – ') }}</b>
                    <small>{{ \Illuminate\Support\Str::after($s, ' – ') }}</small>
                </div>
            @endforeach
        </div>
        <div>
            <h4>Khám phá</h4>
            <a href="{{ route('home') }}">Trang chủ</a>
            <a href="{{ route('cars.index') }}">Tất cả xe</a>
            @foreach($navCategories as $c)<a href="{{ route('cars.category', $c) }}">{{ $c->name }}</a>@endforeach
            <a href="{{ route('cars.compare') }}">So sánh xe</a>
        </div>
        <div>
            <h4>Liên hệ</h4>
            <a href="tel:19006868"><x-icon name="phone"/> {{ config('autoshop.hotline') }}</a>
            <span class="footer-note">8:00 – 20:00 mỗi ngày</span>
            <a href="{{ route('cart.index') }}"><x-icon name="cart"/> Giỏ xe</a>
            @auth<a href="{{ route('account.orders') }}"><x-icon name="file"/> Tra cứu đơn đặt xe</a>@else<a href="{{ route('register') }}"><x-icon name="user"/> Tạo tài khoản</a>@endauth
        </div>
    </div>
    <div class="footer-bottom">© {{ date('Y') }} AutoShop – Đồ án môn Lập trình mã nguồn mở (Laravel · PHP · MySQL). Giá xe chỉ mang tính tham khảo.</div>
</footer>

<a class="fab-call" href="tel:19006868" title="Gọi tư vấn {{ config('autoshop.hotline') }}" aria-label="Gọi tư vấn {{ config('autoshop.hotline') }}"><x-icon name="phone"/></a>

{{-- Flash message: dùng Toast của Bootstrap 5 --}}
<div class="toast-container toast-wrap" aria-live="polite">
    @if(session('success'))
        <div class="toast" role="status" data-bs-delay="4500">
            <div class="d-flex align-items-start gap-2"><div class="toast-body p-0 flex-grow-1">{{ session('success') }}</div><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Đóng"></button></div>
        </div>
    @endif
    @if(session('error'))
        <div class="toast bad" role="alert" data-bs-delay="6000">
            <div class="d-flex align-items-start gap-2"><div class="toast-body p-0 flex-grow-1">{{ session('error') }}</div><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Đóng"></button></div>
        </div>
    @endif
</div>

@if($compareCount && ! request()->routeIs('cars.compare'))
    <div class="compare-bar"><x-icon name="compare"/> Đang chọn {{ $compareCount }}/3 xe để so sánh <a class="btn btn-sm" href="{{ route('cars.compare') }}">So sánh ngay</a></div>
@endif

@include('components.confirm-modal')
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}?v=3"></script>
@stack('scripts')
</body>
</html>
