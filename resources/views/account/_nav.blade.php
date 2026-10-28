<nav class="tabs" style="margin-top:20px" aria-label="Tài khoản">
    <a href="{{ route('account.profile') }}" class="{{ request()->routeIs('account.profile') ? 'active' : '' }}">Thông tin cá nhân</a>
    <a href="{{ route('account.orders') }}" class="{{ request()->routeIs('account.orders') ? 'active' : '' }}">Đơn đặt xe</a>
    <a href="{{ route('account.testdrives') }}" class="{{ request()->routeIs('account.testdrives') ? 'active' : '' }}">Lịch lái thử</a>
    <a href="{{ route('account.wishlist') }}" class="{{ request()->routeIs('account.wishlist') ? 'active' : '' }}">Xe yêu thích</a>
</nav>
