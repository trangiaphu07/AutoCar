@extends('layouts.admin')

@section('title', 'Đơn '.$order->code)

@section('content')
<div class="page-head">
    <div><h1>Đơn #{{ $order->code }}</h1><small>Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }} · {{ $order->paymentMethodLabel() }}</small></div>
    <div style="display:flex;gap:8px;align-items:center"><x-order-badge :order="$order"/><a class="btn btn-ghost btn-sm" href="{{ route('admin.orders.index') }}">← Danh sách</a></div>
</div>

<div class="form-grid">
    <div>
        <div class="card card-body">
            <h3>Xe đặt mua</h3>
            <div class="table-wrap table-responsive">
                <table class="table table-hover align-middle">
                    <tr><th>Xe</th><th>Mã</th><th class="num">Đơn giá</th><th class="num">SL</th><th class="num">Thành tiền</th></tr>
                    @foreach($order->items as $i)
                        <tr><td><b>{{ $i->name }}</b><br><small>{{ $i->color }} · {{ $i->year }}</small></td><td>{{ $i->sku }}</td><td class="num">{{ vnd($i->price) }}</td><td class="num">{{ $i->quantity }}</td><td class="num">{{ vnd($i->lineTotal()) }}</td></tr>
                    @endforeach
                </table>
            </div>
            <div style="max-width:380px;margin-left:auto;margin-top:12px">
                <div class="summary-row"><span>Giá xe</span><b>{{ vnd($order->subtotal) }}</b></div>
                <div class="summary-row"><span>Chi phí lăn bánh</span><b>{{ vnd($order->registration_fee) }}</b></div>
                <div class="summary-row total"><span>Tổng</span><span>{{ vnd($order->total) }}</span></div>
                <div class="summary-row"><span>Tiền cọc</span><b>{{ vnd($order->deposit_amount) }}</b></div>
                <div class="summary-row"><span>Còn lại</span><b style="color:var(--accent)">{{ vnd($order->remainingAmount()) }}</b></div>
            </div>
        </div>

        <div class="card card-body" style="margin-top:16px">
            <h3>Giao dịch thanh toán</h3>
            <div class="table-wrap table-responsive">
                <table class="table table-hover align-middle">
                    <tr><th>Mã giao dịch</th><th>Kênh</th><th class="num">Số tiền</th><th>Trạng thái</th><th>Mã cổng</th><th>Thời điểm</th></tr>
                    @foreach($order->transactions as $t)
                        <tr>
                            <td><code>{{ $t->transaction_code }}</code></td>
                            <td>{{ $t->gatewayLabel() }}</td>
                            <td class="num">{{ vnd($t->amount) }}</td>
                            <td><span class="badge {{ ['pending' => 'badge-warn', 'success' => 'badge-ok', 'failed' => 'badge-bad'][$t->status] }}">{{ ['pending' => 'Chờ', 'success' => 'Thành công', 'failed' => 'Thất bại'][$t->status] }}</span></td>
                            <td>{{ $t->gateway_transaction_no ?? '—' }}</td>
                            <td>{{ $t->paid_at?->format('H:i d/m/Y') ?? $t->created_at->format('H:i d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="card card-body">
            <h3>Khách hàng</h3>
            <p style="margin:0">
                <b>{{ $order->customer_name }}</b>@if($order->user) <span class="badge">Có tài khoản</span>@endif<br>
                {{ $order->customer_phone }}<br>
                {{ $order->customer_email }}<br>
                @if($order->customer_id_number)CCCD: {{ $order->customer_id_number }}<br>@endif
                {{ $order->address }}
            </p>
            <hr>
            <small>Showroom nhận xe</small><p style="margin:0"><b>{{ $order->showroom }}</b></p>
            @if($order->note)<hr><small>Ghi chú</small><p style="margin:0">{{ $order->note }}</p>@endif
        </div>

        @if($order->awaitingTransfer())
            @php $pendingTx = $order->transactions->first(fn ($t) => $t->gateway === 'bank_transfer' && $t->status === 'pending'); @endphp
            <div class="card card-body" style="margin-top:16px">
                <h3>Chuyển khoản tiền cọc</h3>
                <p class="muted">Kiểm tra tài khoản <b>{{ config('autoshop.bank.account_no') }}</b> ({{ config('autoshop.bank.bank_name') }}) đã nhận <b>{{ vnd($order->deposit_amount) }}</b>@if($pendingTx) với nội dung <code>{{ $pendingTx->transaction_code }}</code>@endif chưa.</p>
                <form method="post" action="{{ route('admin.orders.confirm-transfer', $order) }}" data-confirm="Xác nhận đã nhận {{ vnd($order->deposit_amount) }} tiền cọc cho đơn {{ $order->code }}?">
                    @csrf
                    <div class="field"><label for="bank_ref">Mã giao dịch ngân hàng (không bắt buộc)</label><input class="input" id="bank_ref" name="bank_ref" maxlength="100" placeholder="Ví dụ: FT26295123456"></div>
                    <button class="btn btn-block" type="submit">✓ Xác nhận đã nhận tiền</button>
                </form>
            </div>
        @endif

        <div class="card card-body" style="margin-top:16px">
            <h3>Xử lý đơn</h3>
            @php $next = \App\Models\Order::TRANSITIONS[$order->status]; @endphp
            @if(empty($next))
                <p class="muted">Đơn đã ở trạng thái cuối ({{ $order->statusLabel() }}).</p>
            @else
                <p class="muted">Chỉ chuyển được theo bước hợp lệ. Hủy đơn sẽ trả xe về kho.</p>
                <div style="display:grid;gap:8px">
                    @foreach($next as $s)
                        <form method="post" action="{{ route('admin.orders.status', $order) }}" @if($s === 'cancelled') data-confirm="Hủy đơn {{ $order->code }} và trả xe về kho?" @endif>
                            @csrf @method('patch')
                            <input type="hidden" name="status" value="{{ $s }}">
                            <button class="btn btn-block {{ $s === 'cancelled' ? 'btn-ghost' : '' }}" type="submit">→ {{ \App\Models\Order::STATUSES[$s] }}</button>
                        </form>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
