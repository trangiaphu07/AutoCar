@extends('layouts.app')

@section('title', 'Đơn đặt xe của tôi – AutoShop')

@section('content')
<div class="container" style="padding-bottom:48px">
    @include('account._nav')
    <h1>Đơn đặt xe của tôi</h1>
    @if($orders->isEmpty())
        <div class="card empty"><h3>Bạn chưa có đơn đặt xe nào</h3><a class="btn" href="{{ route('cars.index') }}">Chọn xe ngay</a></div>
    @else
        <div class="card table-wrap table-responsive">
            <table class="table table-hover align-middle">
                <tr><th>Mã đơn</th><th>Ngày đặt</th><th>Xe</th><th class="num">Tổng</th><th class="num">Tiền cọc</th><th>Trạng thái</th><th></th></tr>
                @foreach($orders as $o)
                    <tr>
                        <td><a href="{{ route('orders.result', $o->code) }}"><b>{{ $o->code }}</b></a></td>
                        <td>{{ $o->created_at->format('d/m/Y') }}</td>
                        <td>{{ $o->items->pluck('name')->implode(', ') }}</td>
                        <td class="num">{{ vnd($o->total) }}</td>
                        <td class="num">{{ vnd($o->deposit_amount) }}</td>
                        <td><x-order-badge :order="$o"/></td>
                        <td>
                            @if($o->canBeCancelledByCustomer())
                                <form method="post" action="{{ route('account.orders.cancel', $o->code) }}" data-confirm="Hủy đơn {{ $o->code }}? Xe sẽ được trả lại cho người khác đặt.">@csrf<button class="btn btn-ghost btn-sm" type="submit">Hủy đơn</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        {{ $orders->links() }}
    @endif
</div>
@endsection
