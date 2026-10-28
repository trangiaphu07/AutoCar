@extends('layouts.app')

@section('content')
@php
    $heroCars = $featured->isNotEmpty() ? $featured : $latest->take(4);
    $showcase = $categories->filter(fn ($c) => $c->cars->isNotEmpty());
@endphp

<h1 class="visually-hidden">AutoShop – showroom ô tô trực tuyến: xe mới, xe đã qua sử dụng, đặt cọc online</h1>

{{-- ---------- Banner trình chiếu xe nổi bật ---------- --}}
<section class="lux-hero" aria-label="Xe nổi bật">
    <div class="hero-slides">
        @foreach($heroCars->take(6) as $i => $car)
            <a class="hero-slide {{ $i === 0 ? 'active' : '' }}" href="{{ route('cars.show', $car->slug) }}" aria-label="{{ $car->name }}">
                <div class="lux-media">
                    @if($car->imageUrl())
                        <img src="{{ $car->imageUrl() }}" alt="" @if($i > 0) loading="lazy" @endif>
                    @else
                        <x-car-art :type="body_type($car->category?->slug)" :color="$car->color_hex" :label="$car->name"/>
                    @endif
                </div>
                <div class="container lux-caption">
                    <span class="eyebrow">{{ $car->brand?->name }} · {{ $car->category?->name }}</span>
                    <h2 class="lux-title">{{ $car->name }}</h2>
                </div>
                <div class="lux-price">
                    <small>{{ $car->discountPercent() ? 'Ưu đãi' : 'Giá từ' }}</small>
                    @if($car->discountPercent())<s>{{ vnd($car->price) }}</s>@endif
                    <b>{{ vnd($car->effectivePrice()) }}</b>
                </div>
            </a>
        @endforeach
    </div>
    @if($heroCars->count() > 1)
        <button class="hero-arrow prev" type="button" data-hero-prev aria-label="Xe trước">‹</button>
        <button class="hero-arrow next" type="button" data-hero-next aria-label="Xe tiếp theo">›</button>
        <div class="hero-dots">
            @foreach($heroCars->take(6) as $i => $car)<button type="button" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Xe nổi bật {{ $i + 1 }}"></button>@endforeach
        </div>
    @endif
</section>

{{-- ---------- Các dòng xe nổi bật (tab theo dòng xe) ---------- --}}
<section class="section lux-sec" data-tabs>
    <div class="container">
        <h2>Các dòng xe nổi bật</h2>
        <div class="lux-tabs" role="tablist">
            <button class="active" type="button" data-tab="show-featured"><x-icon name="tag"/> Xe nổi bật <span>({{ $featured->count() }})</span></button>
            @foreach($showcase as $c)
                <button type="button" data-tab="show-{{ $c->slug }}"><x-icon name="car"/> {{ $c->name }} <span>({{ $c->cars_count }})</span></button>
            @endforeach
        </div>

        <div class="tab-panel active" id="show-featured">
            <div class="car-rail">@foreach($featured as $car)<x-car-card :car="$car"/>@endforeach</div>
        </div>
        @foreach($showcase as $c)
            <div class="tab-panel" id="show-{{ $c->slug }}">
                <div class="car-rail">@foreach($c->cars as $car)<x-car-card :car="$car"/>@endforeach</div>
                <a class="link-arrow" href="{{ route('cars.category', $c) }}">Xem tất cả {{ $c->name }} ↗</a>
            </div>
        @endforeach
    </div>
</section>

{{-- ---------- Tìm xe nhanh ---------- --}}
<section class="lux-sec lux-chooser">
    <div class="container">
        <div class="chooser-card">
            <span class="eyebrow">AutoShop</span>
            <h2>Bảng giá & ưu đãi mới nhất</h2>
            <p class="muted">Tư vấn xe có sẵn, trả góp và lịch lái thử tại {{ count(config('autoshop.showrooms')) }} showroom AutoShop.</p>

            <form class="finder" action="{{ route('cars.index') }}" method="get">
                <div>
                    <label for="f-brand">Hãng xe</label>
                    <select class="input" id="f-brand" name="brand[]">
                        <option value="">Tất cả hãng</option>
                        @foreach($brands as $b)<option value="{{ $b->slug }}">{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="f-price">Khoảng giá</label>
                    <select class="input" id="f-price" name="price">
                        <option value="">Mọi mức giá</option>
                        @foreach(\App\Http\Controllers\ShopController::PRICE_RANGES as $k => $r)<option value="{{ $k }}">{{ $r[0] }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="f-cond">Tình trạng</label>
                    <select class="input" id="f-cond" name="condition">
                        <option value="">Mới & đã qua sử dụng</option>
                        @foreach(\App\Models\Car::CONDITIONS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <button class="btn" type="submit"><x-icon name="search"/> Tìm xe</button>
            </form>

            <small class="chooser-label">Bạn đang tìm xe nào?</small>
            <div class="chooser-grid">
                @foreach($categories as $c)
                    <a href="{{ route('cars.category', $c) }}"><x-icon name="car"/> {{ $c->name }}</a>
                @endforeach
                <a href="{{ route('cars.index', ['condition' => 'used']) }}"><x-icon name="key"/> Xe đã qua sử dụng</a>
                <a href="tel:19006868"><x-icon name="phone"/> Cần tư vấn</a>
            </div>
        </div>
    </div>
</section>

{{-- ---------- Giới thiệu từng mẫu xe ---------- --}}
@if($heroCars->isNotEmpty())
<section class="section lux-sec">
    <div class="container">
        <div class="section-center">
            <span class="eyebrow">Mới ra mắt</span>
            <h2>Dòng xe nổi bật</h2>
            <p class="muted">Những mẫu xe mới nhất tại AutoShop, sẵn xe giao ngay và hỗ trợ trả góp đến 80%.</p>
        </div>

        @foreach($heroCars->take(3) as $car)
            @php
                $stats = collect([
                    ['Số chỗ', $car->spec('so_cho')],
                    ['Nhiên liệu', $car->spec('nhien_lieu')],
                    ['Hộp số', $car->spec('hop_so') ? \Illuminate\Support\Str::before($car->spec('hop_so'), ' vô cấp') : null],
                    ['Đời xe', (string) $car->year],
                ])->filter(fn ($s) => filled($s[1]))->take(3);
            @endphp
            <article class="spot {{ $loop->odd ? '' : 'reverse' }}">
                <div class="spot-text">
                    <span class="eyebrow">{{ $loop->first ? 'Mới nhất' : $car->brand?->name }}</span>
                    <h3>{{ $car->name }}</h3>
                    <p>{{ $car->short_description ?: 'Mẫu '.$car->category?->name.' của '.$car->brand?->name.', đời '.$car->year.'.' }}</p>
                    <div class="spot-stats">
                        @foreach($stats as [$label, $value])<div><b>{{ $value }}</b><small>{{ $label }}</small></div>@endforeach
                    </div>
                    <div class="spot-cta">
                        <a class="btn" href="{{ route('cars.show', $car->slug) }}#lai-thu"><x-icon name="calendar"/> Đặt lịch lái thử</a>
                        <a class="link-arrow" href="{{ route('cars.show', $car->slug) }}">Khám phá {{ vnd_short($car->effectivePrice()) }} ↗</a>
                    </div>
                </div>
                <a class="spot-media" href="{{ route('cars.show', $car->slug) }}" aria-label="{{ $car->name }}">
                    @if($car->imageUrl())
                        <img src="{{ $car->imageUrl() }}" alt="{{ $car->name }}" loading="lazy">
                    @else
                        <x-car-art :type="body_type($car->category?->slug)" :color="$car->color_hex" :label="$car->name"/>
                    @endif
                </a>
            </article>
        @endforeach
    </div>
</section>
@endif

@if($deals->isNotEmpty())
<section class="section lux-sec">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Ưu đãi tháng {{ now()->month }}</span><h2>Đang giảm giá</h2></div>
            <a class="link-arrow" href="{{ route('cars.index', ['sort' => 'popular']) }}">Xem tất cả ↗</a>
        </div>
        <div class="car-grid cols-4">
            @foreach($deals as $car)<x-car-card :car="$car"/>@endforeach
        </div>
    </div>
</section>
@endif

{{-- ---------- Vì sao chọn AutoShop ---------- --}}
<section class="section lux-sec">
    <div class="container">
        <div class="section-center">
            <span class="eyebrow">Vì sao chọn AutoShop</span>
            <h2>Ưu điểm vượt trội</h2>
        </div>
        <div class="perks">
            <div class="perk"><span class="icon-circle"><x-icon name="shield"/></span><b>Bảo hành chính hãng</b><small>Xe mới 3 năm, xe đã qua sử dụng 12 tháng.</small></div>
            <div class="perk"><span class="icon-circle"><x-icon name="key"/></span><b>Lái thử miễn phí</b><small>Đăng ký online, chọn showroom và giờ phù hợp.</small></div>
            <div class="perk"><span class="icon-circle"><x-icon name="card"/></span><b>Trả góp đến 80%</b><small>Duyệt hồ sơ trong 24 giờ, lãi suất ưu đãi.</small></div>
            <div class="perk"><span class="icon-circle"><x-icon name="truck"/></span><b>Giao xe tận nhà</b><small>Trọn gói thủ tục đăng ký, biển số, bảo hiểm.</small></div>
        </div>
    </div>
</section>

<section class="section lux-sec">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">Thương hiệu</span><h2>Hãng xe phân phối</h2></div></div>
        <div class="brand-strip">
            @foreach($brands as $b)
                <a class="brand-chip" href="{{ route('cars.index', ['brand' => [$b->slug]]) }}">{{ $b->name }}<small>{{ $b->cars_count }} mẫu · {{ $b->country }}</small></a>
            @endforeach
        </div>
    </div>
</section>

{{-- ---------- Kêu gọi lái thử ---------- --}}
<section class="cta-band">
    <div class="container">
        <span class="eyebrow">Sẵn sàng trải nghiệm</span>
        <h2>Đặt lịch lái thử cùng đội ngũ AutoShop</h2>
        <span class="gold-rule"></span>
        <p>Chuyên viên tư vấn sẽ liên hệ trong vòng 24 giờ để sắp xếp buổi lái thử riêng cho bạn.</p>
        <div class="cta-actions">
            <a class="btn" href="{{ route('cars.index') }}">Chọn xe lái thử</a>
            <a class="link-arrow" href="tel:19006868">Gọi {{ config('autoshop.hotline') }} ↗</a>
        </div>
    </div>
</section>
@endsection
