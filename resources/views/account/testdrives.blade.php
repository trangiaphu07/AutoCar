@extends('layouts.app')

@section('title', 'Lịch lái thử – AutoShop')

@section('content')
<div class="container" style="padding-bottom:48px">
    @include('account._nav')
    <h1>Lịch lái thử</h1>
    @if($drives->isEmpty())
        <div class="card empty"><h3>Bạn chưa đăng ký lái thử</h3><p>Vào trang chi tiết xe, chọn thẻ “Đăng ký lái thử”.</p></div>
    @else
        <div class="card table-wrap table-responsive">
            <table class="table table-hover align-middle">
                <tr><th>Xe</th><th>Ngày giờ</th><th>Showroom</th><th>Trạng thái</th></tr>
                @foreach($drives as $d)
                    <tr>
                        <td><b>{{ $d->car?->name }}</b></td>
                        <td>{{ $d->preferred_date->format('d/m/Y') }} lúc {{ $d->preferred_time }}</td>
                        <td>{{ $d->showroom }}</td>
                        <td><span class="badge {{ ['new' => 'badge-warn', 'confirmed' => 'badge-info', 'done' => 'badge-ok', 'cancelled' => 'badge-bad'][$d->status] }}">{{ $d->statusLabel() }}</span></td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif
</div>
@endsection
