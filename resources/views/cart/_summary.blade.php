<aside class="card card-body" style="position:sticky;top:130px">
    <h3>Tóm tắt chi phí</h3>
    <div class="summary-row"><span>Giá xe ({{ $summary['items']->sum('quantity') }} chiếc)</span><b>{{ vnd($summary['subtotal']) }}</b></div>
    <div class="summary-row"><span>Chi phí lăn bánh ước tính<br><small>Lệ phí trước bạ, biển số, đăng kiểm, bảo hiểm TNDS</small></span><b>{{ vnd($summary['registration']) }}</b></div>
    <div class="summary-row total"><span>Tổng giá lăn bánh</span><span>{{ vnd($summary['total']) }}</span></div>
    <div class="summary-row deposit"><span>Đặt cọc trước ({{ config('autoshop.deposit_percent') }}%)</span><span>{{ vnd($summary['deposit']) }}</span></div>
    <small style="display:block;margin-top:10px">Phần còn lại ({{ vnd($summary['total'] - $summary['deposit']) }}) thanh toán khi nhận xe hoặc qua ngân hàng nếu mua trả góp.</small>
    @if(! empty($action))
        <a class="btn btn-block" href="{{ route('checkout.form') }}" style="margin-top:16px">Tiến hành đặt cọc →</a>
    @endif
</aside>
