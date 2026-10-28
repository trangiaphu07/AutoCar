@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="page-head">
    <div>
        <h1>Quản lý người dùng</h1>
        <p class="muted">Danh sách tài khoản khách hàng và quản trị viên hệ thống</p>
    </div>
    <a class="btn" href="{{ route('admin.users.create') }}">+ Thêm tài khoản</a>
</div>

<div class="grid-3" style="margin-bottom:20px">
    <div class="card stat-card">
        <span class="muted">Tổng tài khoản</span>
        <div class="value">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="card stat-card">
        <span class="muted">Quản trị viên</span>
        <div class="value" style="color:var(--accent)">{{ number_format($stats['admins']) }}</div>
    </div>
    <div class="card stat-card">
        <span class="muted">Khách hàng</span>
        <div class="value" style="color:var(--ok)">{{ number_format($stats['customers']) }}</div>
    </div>
</div>

<form class="filters-row" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, email, số điện thoại…">
    <select class="input" name="role" data-autosubmit>
        <option value="">Tất cả vai trò</option>
        <option value="customer" @selected(request('role') === 'customer')>Khách hàng</option>
        <option value="admin" @selected(request('role') === 'admin')>Quản trị viên</option>
    </select>
    <button class="btn btn-ghost" type="submit">Lọc</button>
    @if(request()->hasAny(['q', 'role']))
        <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Đặt lại</a>
    @endif
</form>

<div class="card table-wrap table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Họ và tên / Email</th>
                <th>Số điện thoại</th>
                <th>Vai trò</th>
                <th class="num">Đơn đặt xe</th>
                <th class="num">Lịch lái thử</th>
                <th>Ngày tạo</th>
                <th style="width:140px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td>
                        <b>{{ $u->name }}</b>
                        @if($u->id === auth()->id())
                            <span class="badge badge-accent" style="font-size:11px;margin-left:4px">Bạn</span>
                        @endif
                        <br><small class="muted">{{ $u->email }}</small>
                    </td>
                    <td>{{ $u->phone ?: '—' }}</td>
                    <td>
                        @if($u->isAdmin())
                            <span class="badge badge-accent">Quản trị viên</span>
                        @else
                            <span class="badge badge-info">Khách hàng</span>
                        @endif
                    </td>
                    <td class="num">
                        @if($u->orders_count > 0)
                            <a href="{{ route('admin.orders.index', ['q' => $u->email]) }}"><b>{{ $u->orders_count }}</b></a>
                        @else
                            <span class="muted">0</span>
                        @endif
                    </td>
                    <td class="num">
                        @if($u->test_drives_count > 0)
                            <b>{{ $u->test_drives_count }}</b>
                        @else
                            <span class="muted">0</span>
                        @endif
                    </td>
                    <td><small>{{ $u->created_at->format('d/m/Y H:i') }}</small></td>
                    <td style="white-space:nowrap;text-align:right">
                        <a class="btn btn-ghost btn-sm" href="{{ route('admin.users.edit', $u) }}">Sửa</a>
                        @if($u->id !== auth()->id())
                            <form class="inline-form" method="post" action="{{ route('admin.users.destroy', $u) }}" data-confirm="Bạn có chắc chắn muốn xóa tài khoản {{ $u->name }}?">
                                @csrf
                                @method('delete')
                                <button class="btn btn-ghost btn-sm" type="submit" style="color:var(--bad)">Xóa</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted" style="text-align:center;padding:30px">Không tìm thấy người dùng nào phù hợp.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $users->links() }}
@endsection
