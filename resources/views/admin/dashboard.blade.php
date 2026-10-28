@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-head">
    <h1>Dashboard</h1>
    <small>Cập nhật lúc {{ now()->format('H:i d/m/Y') }}</small>
</div>

<div class="stats">
    <div class="card stat"><small>Doanh số xe đã chốt</small><b>{{ vnd_short($revenue) }}</b><span class="sub">Đơn đã cọc / đã thanh toán, chưa hủy</span></div>
    <div class="card stat"><small>Tiền đã thu</small><b>{{ vnd_short($collected) }}</b><span class="sub">Tổng giao dịch thành công</span></div>
    <div class="card stat"><small>Đơn đặt xe</small><b>{{ $orderCount }}</b><span class="sub">{{ $byStatus['pending'] ?? 0 }} đơn chờ xử lý</span></div>
    <div class="card stat"><small>Xe đang bán</small><b>{{ $carCount }} mẫu</b><span class="sub">{{ $stockTotal }} chiếc trong kho</span></div>
</div>

<div class="dash-grid">
    <div class="card card-body">
        <div class="card-title"><h3>Doanh số 6 tháng gần nhất</h3></div>
        @php $max = max(1, $monthly->max('value')); @endphp
        <div class="bars">
            @foreach($monthly as $m)
                <div class="bar">
                    <em>{{ $m['value'] ? vnd_short($m['value']) : '' }}</em>
                    <i style="height:{{ round($m['value'] / $max * 100) }}%"></i>
                    <span>{{ $m['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card card-body">
        <div class="card-title"><h3>Đơn theo trạng thái</h3><a class="btn btn-ghost btn-sm" href="{{ route('admin.orders.index') }}">Xem đơn</a></div>
        <table class="table table-hover align-middle">
            @foreach(\App\Models\Order::STATUSES as $k => $label)
                <tr><td>{{ $label }}</td><td class="num"><b>{{ $byStatus[$k] ?? 0 }}</b></td></tr>
            @endforeach
        </table>
        <h3 style="margin-top:16px">Tỉ lệ thanh toán thành công theo kênh</h3>
        <table class="table table-hover align-middle">
            @forelse($gateways as $g => $row)
                <tr><td>{{ \App\Models\PaymentTransaction::GATEWAYS[$g] ?? $g }}</td><td>{{ $row['ok'] }}/{{ $row['all'] }} giao dịch</td><td style="width:40%"><div class="meter {{ $row['rate'] < 50 ? 'low' : ($row['rate'] < 80 ? 'mid' : '') }}"><i style="width:{{ $row['rate'] }}%"></i></div></td><td class="num">{{ $row['rate'] }}%</td></tr>
            @empty
                <tr><td class="muted">Chưa có giao dịch.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="card card-body">
        <div class="card-title"><h3>Xe bán chạy</h3></div>
        <table class="table table-hover align-middle">
            <tr><th>Mẫu xe</th><th class="num">Số xe</th><th class="num">Giá trị</th></tr>
            @forelse($topCars as $t)
                <tr><td>{{ $t->name }}</td><td class="num">{{ $t->qty }}</td><td class="num">{{ vnd_short($t->amount) }}</td></tr>
            @empty
                <tr><td colspan="3" class="muted">Chưa có dữ liệu.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="card card-body">
        <div class="card-title"><h3>Lịch lái thử mới</h3><a class="btn btn-ghost btn-sm" href="{{ route('admin.testdrives.index') }}">Tất cả</a></div>
        <table class="table table-hover align-middle">
            @forelse($newTestDrives as $d)
                <tr><td><b>{{ $d->name }}</b><br><small>{{ $d->phone }}</small></td><td>{{ $d->car?->name }}</td><td>{{ $d->preferred_date->format('d/m') }} {{ $d->preferred_time }}</td></tr>
            @empty
                <tr><td class="muted">Không có yêu cầu mới.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="card card-body">
        <div class="card-title"><h3>Sắp hết xe (≤ 2 chiếc)</h3></div>
        <table class="table table-hover align-middle">
            @forelse($lowStock as $c)
                <tr><td><a href="{{ route('admin.cars.edit', $c) }}">{{ $c->name }}</a></td><td class="num"><span class="badge {{ $c->stock === 0 ? 'badge-bad' : 'badge-warn' }}">{{ $c->stock }} chiếc</span></td></tr>
            @empty
                <tr><td class="muted">Kho đủ xe.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="card card-body">
        <div class="card-title"><h3>Dữ liệu xe chưa hoàn thiện</h3></div>
        <table class="table table-hover align-middle">
            @forelse($incomplete as $c)
                <tr><td><a href="{{ route('admin.cars.edit', $c) }}">{{ $c->name }}</a><br><small>{{ \App\Models\Car::STATUSES[$c->status] }}</small></td><td style="width:40%"><div class="meter {{ $c->completeness < 60 ? 'low' : 'mid' }}"><i style="width:{{ $c->completeness }}%"></i></div></td><td class="num">{{ $c->completeness }}%</td></tr>
            @empty
                <tr><td class="muted">Tất cả xe đã đủ dữ liệu.</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="card card-body" style="margin-top:20px">
    <div class="card-title"><h3>Đơn mới nhất</h3></div>
    <div class="table-wrap table-responsive">
        <table class="table table-hover align-middle">
            <tr><th>Mã</th><th>Khách hàng</th><th>Ngày</th><th class="num">Tổng</th><th>Trạng thái</th></tr>
            @foreach($recentOrders as $o)
                <tr><td><a href="{{ route('admin.orders.show', $o) }}"><b>{{ $o->code }}</b></a></td><td>{{ $o->customer_name }}</td><td>{{ $o->created_at->format('d/m/Y H:i') }}</td><td class="num">{{ vnd($o->total) }}</td><td><x-order-badge :order="$o"/></td></tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
