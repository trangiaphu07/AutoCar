@extends('layouts.app')

@section('title', 'Giỏ xe – AutoShop')

@section('content')
<div class="container">
    <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> / <span>Giỏ xe</span></div>

    @if($summary['items']->isEmpty())
        <div class="card empty" style="margin:24px 0 48px">
            <x-car-art type="hatch" color="#9aa3ad" style="max-width:240px;margin:0 auto 10px;opacity:.6"/>
            <h3>Giỏ xe đang trống</h3>
            <p>Hãy chọn một mẫu xe bạn yêu thích để đặt cọc.</p>
            <a class="btn" href="{{ route('cars.index') }}">Khám phá xe</a>
        </div>
    @else
        <div class="two-col">
            <div>
                <h1>Giỏ xe của bạn</h1>
                <div class="card">
                    @foreach($summary['items'] as $line)
                        <div class="line-item">
                            <a class="thumb" href="{{ route('cars.show', $line->car->slug) }}">
                                @if($line->car->imageUrl())
                                    <img src="{{ $line->car->imageUrl() }}" alt="{{ $line->car->name }}" loading="lazy">
                                @else
                                    <x-car-art :type="body_type($line->car->category->slug)" :color="$line->car->color_hex"/>
                                @endif
                            </a>
                            <div>
                                <small>{{ $line->car->brand?->name }} · {{ $line->car->year }} · {{ $line->car->color }}</small>
                                <h3 style="margin:2px 0 6px"><a href="{{ route('cars.show', $line->car->slug) }}">{{ $line->car->name }}</a></h3>
                                <div class="price" style="font-size:16px">{{ vnd($line->price) }}</div>
                                <small>Còn {{ $line->car->stock }} chiếc tại đại lý</small>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
                                <form method="post" action="{{ route('cart.update', $line->car) }}" class="qty">
                                    @csrf @method('patch')
                                    <input class="input" type="number" name="quantity" min="1" max="{{ min($line->car->stock, config('autoshop.max_qty_per_car')) }}" value="{{ $line->quantity }}" aria-label="Số lượng" data-autosubmit>
                                </form>
                                <b>{{ vnd($line->total) }}</b>
                                <form method="post" action="{{ route('cart.remove', $line->car) }}">@csrf @method('delete')<button class="link-btn" type="submit">Xóa</button></form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p style="margin-top:14px"><a href="{{ route('cars.index') }}">← Tiếp tục xem xe</a></p>
            </div>

            @include('cart._summary', ['summary' => $summary, 'action' => true])
        </div>
    @endif
</div>
@endsection
