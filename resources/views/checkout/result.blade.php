@extends('layouts.app')

@section('title', 'Đơn '.$order->code.' – AutoShop')

@section('content')
@php
    $flow = ['pending', 'confirmed', 'delivering', 'completed'];
    $pos = array_search($order->status, $flow, true);
@endphp
<div class="container" style="max-width:900px;padding:28px 16px 48px">
    <div class="card card-body">
        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:start">
            <div>
                <span class="eyebrow">Đơn đặt xe</span>
                <h1 style="margin-bottom:4px">#{{ $order->code }}</h1>
                <small>Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }} · {{ $order->paymentMethodLabel() }}</small>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <x-order-badge :order="$order"/>
            </div>
        </div>

        <ol class="steps">
            @if($order->status === 'cancelled')
                <li class="done">Đặt xe</li><li class="cancel">Đã hủy</li>
            @else
                @foreach(['Đặt xe', 'Xác nhận & cọc', 'Làm thủ tục', 'Bàn giao xe'] as $i => $label)
                    <li class="{{ $pos !== false && $i <= $pos ? 'done' : '' }}">{{ $label }}</li>
                @endforeach
            @endif
        </ol>

        @if($order->payment_method === 'vnpay' && $order->canBeCancelledByCustomer())
            <div class="alert alert-info" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
                <span>Đơn chưa được đặt cọc. Vui lòng thanh toán trong {{ config('autoshop.payment_expire_minutes') }} phút kể từ khi đặt để giữ xe.</span>
                <form method="post" action="{{ route('orders.retry', $order->code) }}">@csrf<button class="btn btn-sm" type="submit">Thanh toán cọc {{ vnd($order->deposit_amount) }}</button></form>
            </div>
        @elseif($transfer)
            <div class="transfer-box">
                <img src="{{ $qrUrl }}" alt="Mã VietQR chuyển khoản tiền cọc đơn {{ $order->code }}" width="240" height="240" loading="lazy">
                <div>
                    <h3 style="margin-top:0">Chuyển khoản tiền cọc</h3>
                    <p class="muted" style="margin-top:0">Mở app ngân hàng, chọn quét mã QR. Số tiền và nội dung đã được điền sẵn.</p>
                    <div class="summary-row"><span>Ngân hàng</span><b>{{ $bank['bank_name'] }}</b></div>
                    <div class="summary-row"><span>Số tài khoản</span><b>{{ $bank['account_no'] }}</b></div>
                    <div class="summary-row"><span>Chủ tài khoản</span><b>{{ $bank['account_name'] }}</b></div>
                    <div class="summary-row"><span>Số tiền</span><b style="color:var(--accent)">{{ vnd($transfer->amount) }}</b></div>
                    <div class="summary-row"><span>Nội dung</span><b><code>{{ $transfer->transaction_code }}</code></b></div>
                    <small class="muted">Vui lòng giữ đúng nội dung chuyển khoản. Đơn sẽ chuyển sang “Đã xác nhận” khi AutoShop đối soát được khoản tiền.</small>
                </div>
            </div>
        @elseif($order->payment_method === 'showroom' && $order->status === 'pending')
            <div class="alert alert-info">Nhân viên AutoShop sẽ gọi số <b>{{ $order->customer_phone }}</b> để hẹn lịch đặt cọc tại <b>{{ $order->showroom }}</b>.</div>
        @endif

        <div class="grid-2" style="margin-top:12px;gap:16px">
            <div>
                <h3>Thông tin khách hàng</h3>
                <p style="margin:0">{{ $order->customer_name }} · {{ $order->customer_phone }}<br>
                    @if($order->customer_email){{ $order->customer_email }}<br>@endif
                    {{ $order->address }}<br>
                    <small>Nhận xe tại: {{ $order->showroom }}</small></p>
            </div>
            <div>
                <h3>Thanh toán</h3>
                <div class="summary-row"><span>Giá xe</span><b>{{ vnd($order->subtotal) }}</b></div>
                <div class="summary-row"><span>Chi phí lăn bánh</span><b>{{ vnd($order->registration_fee) }}</b></div>
                <div class="summary-row total"><span>Tổng</span><span>{{ vnd($order->total) }}</span></div>
                <div class="summary-row"><span>Tiền cọc</span><b>{{ vnd($order->deposit_amount) }}</b></div>
                <div class="summary-row"><span>Còn phải thanh toán</span><b style="color:var(--accent)">{{ vnd($order->remainingAmount()) }}</b></div>
            </div>
        </div>

        <h3 style="margin-top:18px">Xe đã đặt</h3>
        <div class="table-wrap table-responsive">
            <table class="table table-hover align-middle">
                <tr><th>Xe</th><th>Mã</th><th class="num">Đơn giá</th><th class="num">SL</th><th class="num">Thành tiền</th></tr>
                @foreach($order->items as $item)
                    <tr><td><b>{{ $item->name }}</b><br><small>{{ $item->color }} · {{ $item->year }}</small></td><td>{{ $item->sku }}</td><td class="num">{{ vnd($item->price) }}</td><td class="num">{{ $item->quantity }}</td><td class="num">{{ vnd($item->lineTotal()) }}</td></tr>
                @endforeach
            </table>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
            <a class="btn btn-ghost" href="{{ route('cars.index') }}">Tiếp tục xem xe</a>
            @auth<a class="btn btn-dark" href="{{ route('account.orders') }}">Đơn đặt xe của tôi</a>@endauth
        </div>
    </div>
</div>
@endsection
