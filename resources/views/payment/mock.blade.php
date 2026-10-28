@extends('layouts.app')

@section('title', 'Cổng thanh toán giả lập – AutoShop')

@section('content')
<div class="container">
    <div class="card mock-pay">
        <div class="mock-head">
            <small>CỔNG THANH TOÁN GIẢ LẬP · mô phỏng VNPay</small>
            <h2 style="margin:6px 0 0">{{ vnd($tx->amount) }}</h2>
            <small>Tiền đặt cọc cho đơn #{{ $tx->order->code }}</small>
        </div>
        <div class="card-body">
            <div class="summary-row"><span>Mã giao dịch</span><b>{{ $tx->transaction_code }}</b></div>
            <div class="summary-row"><span>Đơn vị nhận</span><b>AutoShop</b></div>
            <div class="summary-row"><span>Nội dung</span><b>Đặt cọc {{ $tx->order->items->pluck('name')->implode(', ') }}</b></div>
            <div class="summary-row"><span>Trạng thái</span><b>{{ ['pending' => 'Chờ thanh toán', 'success' => 'Thành công', 'failed' => 'Thất bại'][$tx->status] }}</b></div>

            <div class="alert alert-info">Trang này thay thế VNPay khi chưa cấu hình <code>VNP_TMN_CODE</code> trong <code>.env</code>. Hai nút dưới đây gọi cùng hàm <code>PaymentService::settle()</code> như VNPay thật.</div>

            @if($tx->status === 'pending')
                <form method="post" action="{{ route('payment.mock.submit', $tx->transaction_code) }}" style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    @csrf
                    <button class="btn btn-ok" name="result" value="success" type="submit">✓ Thanh toán thành công</button>
                    <button class="btn btn-bad" name="result" value="failed" type="submit">✕ Thanh toán thất bại</button>
                </form>
            @else
                <a class="btn btn-block" href="{{ route('orders.result', $tx->order->code) }}">Xem đơn hàng</a>
            @endif
        </div>
    </div>
</div>
@endsection
