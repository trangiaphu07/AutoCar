@props(['car'])
@php
    $fuel = $car->spec('nhien_lieu');
    $gear = $car->spec('hop_so');
    $seats = $car->spec('so_cho');
    $range = $car->spec('quang_duong');
    $saving = $car->discountPercent() ? $car->price - $car->effectivePrice() : 0;
    $isNew = $car->condition === 'new' && $car->year >= now()->year - 1;
@endphp
<article class="card car-card">
    <a class="car-thumb" href="{{ route('cars.show', $car->slug) }}" aria-label="{{ $car->name }}">
        @if($car->imageUrl())
            <img src="{{ $car->imageUrl() }}" alt="{{ $car->name }}" loading="lazy">
        @else
            <x-car-art :type="body_type($car->category?->slug)" :color="$car->color_hex" :label="$car->name"/>
        @endif
        <span class="flags">
            @if($car->discountPercent())<span class="badge badge-accent">-{{ $car->discountPercent() }}%</span>@endif
            @if($car->condition === 'used')<span class="badge">Đã qua sử dụng</span>@endif
            @if($car->stock === 0)<span class="badge badge-bad">Tạm hết xe</span>@elseif($car->stock <= 2)<span class="badge badge-warn">Chỉ còn {{ $car->stock }}</span>@endif
        </span>
    </a>
    <form class="car-compare" method="post" action="{{ route('cars.compare.toggle', $car) }}">@csrf
        <button class="round-btn" type="submit" title="Thêm vào so sánh" aria-label="So sánh {{ $car->name }}"><x-icon name="compare"/></button>
    </form>
    <div class="car-info">
        <span class="brand">{{ $car->brand?->name }} · {{ $car->year }}</span>
        <h3><a href="{{ route('cars.show', $car->slug) }}">{{ $car->name }}</a>@if($isNew) <span class="pill">Mới</span>@endif</h3>
        <div class="chips">
            @if($car->category)<span class="chip">{{ $car->category->name }}</span>@endif
            @if($seats)<span class="chip">{{ $seats }}</span>@endif
            @if($fuel)<span class="chip">{{ $fuel }}</span>@endif
            @if($gear)<span class="chip">{{ \Illuminate\Support\Str::before($gear, ' vô cấp') }}</span>@endif
            @if($range)<span class="chip">{{ $range }} km/lần sạc</span>@endif
            @if($car->condition === 'used')<span class="chip">{{ number_format($car->mileage, 0, ',', '.') }} km</span>@endif
        </div>
        <div class="price-grid">
            <div>
                <small>Giá từ</small>
                @if($car->discountPercent())<div class="price-old">{{ vnd($car->price) }}</div>@endif
                <div class="price">{{ vnd($car->effectivePrice()) }}</div>
            </div>
            <div class="stock-left">
                <small>Còn lại</small>
                <b>{{ $car->stock }} xe</b>
            </div>
        </div>
        @if($saving)
            <div class="deal-box"><x-icon name="tag"/><div><small>Ưu đãi</small><b>Giảm {{ vnd($saving) }}</b></div></div>
        @endif
        <div class="card-cta">
            <a class="btn btn-ghost btn-sm" href="{{ route('cars.show', $car->slug) }}">↗ Khám phá</a>
            <a class="btn btn-sm" href="{{ route('cars.show', $car->slug) }}#lai-thu"><x-icon name="calendar"/> Lái thử</a>
        </div>
    </div>
</article>
