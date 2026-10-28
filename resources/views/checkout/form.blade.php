@extends('layouts.app')

@section('title', 'Đặt cọc xe – AutoShop')

@section('content')
<div class="container">
    <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> / <a href="{{ route('cart.index') }}">Giỏ xe</a> / <span>Đặt cọc</span></div>

    <form method="post" action="{{ route('checkout.place') }}" class="two-col">
        @csrf
        <div>
            <h1>Thông tin đặt xe</h1>
            @if($errors->any())
                <div class="alert alert-bad">Vui lòng kiểm tra lại thông tin:<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <div class="card card-body">
                <h3>1. Người đứng tên xe</h3>
                <div class="grid-2">
                    <div class="field"><label for="customer_name">Họ và tên *</label><input class="input" id="customer_name" name="customer_name" required value="{{ old('customer_name', $user?->name) }}"></div>
                    <div class="field"><label for="customer_phone">Số điện thoại *</label><input class="input" id="customer_phone" name="customer_phone" required value="{{ old('customer_phone', $user?->phone) }}" placeholder="0909123456"></div>
                    <div class="field"><label for="customer_email">Email</label><input class="input" id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', $user?->email) }}"></div>
                    <div class="field"><label for="customer_id_number">Số CCCD (12 số)</label><input class="input" id="customer_id_number" name="customer_id_number" inputmode="numeric" maxlength="12" value="{{ old('customer_id_number') }}" placeholder="Dùng để làm hồ sơ đăng ký xe"></div>
                </div>
                <div class="field"><label for="address">Địa chỉ liên hệ *</label><input class="input" id="address" name="address" required value="{{ old('address') }}" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"></div>
            </div>

            <div class="card card-body" style="margin-top:16px">
                <h3>2. Showroom nhận xe</h3>
                <select class="input" name="showroom" aria-label="Showroom nhận xe">
                    @foreach($showrooms as $s)<option @selected(old('showroom') === $s)>{{ $s }}</option>@endforeach
                </select>
                <div class="field" style="margin-top:14px"><label for="note">Ghi chú</label><textarea class="input" id="note" name="note" placeholder="Ví dụ: muốn mua trả góp 70% qua ngân hàng, cần giao xe trước Tết…">{{ old('note') }}</textarea></div>
            </div>

            <div class="card card-body" style="margin-top:16px">
                <h3>3. Hình thức đặt cọc</h3>
                <label class="pay-option">
                    <input type="radio" name="payment_method" value="vnpay" @checked(old('payment_method', 'vnpay') === 'vnpay')>
                    <span><b>Đặt cọc online qua VNPay {{ $isMock ? '(cổng giả lập để demo)' : '' }}</b><small>Thẻ ATM nội địa, Visa/Master, QR ngân hàng. Giữ xe ngay sau khi thanh toán thành công. Đơn chưa thanh toán sẽ tự hủy sau {{ config('autoshop.payment_expire_minutes') }} phút.</small></span>
                </label>
                <label class="pay-option">
                    <input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')>
                    <span><b>Chuyển khoản ngân hàng (quét mã VietQR)</b><small>Quét mã QR bằng app ngân hàng bất kỳ, số tiền và nội dung đã điền sẵn. Đơn được xác nhận khi AutoShop nhận được tiền cọc.</small></span>
                </label>
                <label class="pay-option">
                    <input type="radio" name="payment_method" value="showroom" @checked(old('payment_method') === 'showroom')>
                    <span><b>Đặt cọc tại showroom</b><small>Nhân viên gọi xác nhận và hẹn lịch đến showroom ký hợp đồng, đặt cọc trực tiếp.</small></span>
                </label>
                <label class="check" style="margin-top:8px"><input type="checkbox" name="agree" value="1" @checked(old('agree'))> Tôi đồng ý với điều khoản đặt cọc: hoàn cọc 100% nếu AutoShop không giao được xe đúng hẹn; khách tự hủy sau khi ký hợp đồng sẽ mất cọc.</label>
            </div>
        </div>

        <div>
            <div class="card card-body" style="margin-bottom:16px">
                <h3>Xe đặt mua</h3>
                @foreach($summary['items'] as $line)
                    <div class="summary-row"><span>{{ $line->car->name }} × {{ $line->quantity }}<br><small>{{ $line->car->color }} · {{ $line->car->year }}</small></span><b>{{ vnd($line->total) }}</b></div>
                @endforeach
            </div>
            @include('cart._summary', ['summary' => $summary])
            <button class="btn btn-block" type="submit" style="margin-top:16px">Xác nhận đặt xe · cọc {{ vnd($summary['deposit']) }}</button>
        </div>
    </form>
</div>
@endsection
