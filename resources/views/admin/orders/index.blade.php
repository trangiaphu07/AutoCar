@extends('layouts.admin')

@section('title', 'Đơn đặt xe')

@section('content')
<div class="page-head"><h1>Đơn đặt xe</h1></div>

<form class="filters-row" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Mã đơn, tên, số điện thoại">
    <select class="input" name="status" data-autosubmit>
        <option value="">Mọi trạng thái đơn</option>
        @foreach(\App\Models\Order::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select class="input" name="payment_status" data-autosubmit>
        <option value="">Mọi trạng thái thanh toán</option>
        @foreach(\App\Models\Order::PAYMENT_STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('payment_status') === $k)>{{ $v }}</option>@endforeach
    </select>
    <button class="btn btn-ghost" type="submit">Lọc</button>
</form>

<div class="card table-wrap table-responsive">
    <table class="table table-hover align-middle">
        <tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th>Hình thức</th><th class="num">Tổng</th><th class="num">Cọc</th><th>Trạng thái</th></tr>
        @forelse($orders as $o)
            <tr>
                <td><a href="{{ route('admin.orders.show', $o) }}"><b>{{ $o->code }}</b></a><br><small>{{ $o->items_count }} xe</small></td>
                <td>{{ $o->customer_name }}<br><small>{{ $o->customer_phone }}</small></td>
                <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ \App\Models\PaymentTransaction::GATEWAYS[$o->payment_method] ?? $o->payment_method }}</td>
                <td class="num">{{ vnd($o->total) }}</td>
                <td class="num">{{ vnd($o->deposit_amount) }}</td>
                <td><x-order-badge :order="$o"/></td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">Không có đơn nào.</td></tr>
        @endforelse
    </table>
</div>
{{ $orders->links() }}
@endsection
