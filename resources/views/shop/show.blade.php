@extends('layouts.app')

@section('title', $car->name.' '.$car->year.' – giá '.vnd_short($car->effectivePrice()).' | AutoShop')
@section('description', $car->short_description)

@section('content')
@php
    $inWishlist = auth()->check() && auth()->user()->wishlist()->where('car_id', $car->id)->exists();
    $inCompare = in_array($car->id, session('compare', []), true);
    $deposit = \App\Services\CartService::depositFor($car->effectivePrice() + \App\Services\CartService::registrationFee($car, 1));
@endphp
<div class="container">
    <div class="breadcrumb">
        <a href="{{ route('home') }}">Trang chủ</a> /
        <a href="{{ route('cars.category', $car->category) }}">{{ $car->category->name }}</a> /
        <span>{{ $car->name }}</span>
    </div>

    <div class="detail">
        <div>
            @php
                $photos = $car->galleryUrls();
            @endphp
            <div class="card gallery car-photo-gallery">
                <div class="gallery-main" style="position:relative;overflow:hidden;border-radius:var(--radius) var(--radius) 0 0">
                    @if(count($photos))
                        <img id="main-car-photo" src="{{ $photos[0] }}" alt="{{ $car->name }}" style="width:100%;height:380px;object-fit:cover;display:block;transition:all .3s ease">
                    @else
                        <x-car-art :type="body_type($car->category->slug)" :color="$car->color_hex" :label="$car->name"/>
                    @endif
                    <span class="flags" style="position:absolute;top:16px;left:16px">
                        @if($car->discountPercent())<span class="badge badge-accent">-{{ $car->discountPercent() }}%</span>@endif
                        @if($car->condition === 'used')<span class="badge badge-info">Đã qua sử dụng</span>@else<span class="badge">Xe mới</span>@endif
                    </span>
                </div>
                @if(count($photos) > 1)
                    <div class="gallery-thumbs" style="display:flex;gap:8px;padding:12px;overflow-x:auto;background:var(--surface-2);border-top:1px solid var(--line)">
                        @foreach($photos as $idx => $photo)
                            <button type="button" class="thumb-btn {{ $idx === 0 ? 'active' : '' }}" onclick="document.getElementById('main-car-photo').src='{{ $photo }}';document.querySelectorAll('.thumb-btn').forEach(function(b){b.style.borderColor='transparent';b.classList.remove('active')});this.style.borderColor='var(--accent)';this.classList.add('active');" style="border:2px solid {{ $idx === 0 ? 'var(--accent)' : 'transparent' }};border-radius:8px;padding:0;background:none;cursor:pointer;overflow:hidden;flex-shrink:0;width:76px;height:52px;transition:border-color .2s ease">
                                <img src="{{ $photo }}" alt="{{ $car->name }} góc {{ $idx + 1 }}" style="width:100%;height:100%;object-fit:cover;display:block">
                            </button>
                        @endforeach
                    </div>
                @endif
                <div class="color-dots" style="padding:10px 16px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--line)">
                    <div><i style="background:{{ $car->color_hex }};display:inline-block;width:12px;height:12px;border-radius:50%;margin-right:6px;vertical-align:middle"></i> Màu {{ $car->color ?? 'đang cập nhật' }}</div>
                    @if(count($photos))
                        <small class="muted" style="font-weight:600"><x-icon name="camera"/> {{ count($photos) }} góc chụp thực tế</small>
                    @endif
                </div>
            </div>

            <div class="card card-body" style="margin-top:20px" data-tabs>
                <div class="tabs" role="tablist">
                    <button class="active" type="button" data-tab="tab-specs">Thông số kỹ thuật</button>
                    <button type="button" data-tab="tab-desc">Mô tả</button>
                    <button type="button" data-tab="tab-loan">Tính trả góp</button>
                    <button type="button" data-tab="tab-drive">Đăng ký lái thử</button>
                </div>

                <div class="tab-panel active" id="tab-specs">
                    <table class="table spec-table">
                        <tr><td>Hãng xe</td><td>{{ $car->brand?->name }} ({{ $car->brand?->country }})</td></tr>
                        <tr><td>Dòng xe</td><td>{{ $car->category->name }}</td></tr>
                        <tr><td>Năm sản xuất</td><td>{{ $car->year }}</td></tr>
                        <tr><td>Tình trạng</td><td>{{ \App\Models\Car::CONDITIONS[$car->condition] }}@if($car->condition === 'used') · đã đi {{ number_format($car->mileage, 0, ',', '.') }} km @endif</td></tr>
                        @foreach($specs as $s)
                            <tr><td>{{ $s['label'] }}</td><td>{{ $s['value'] }}</td></tr>
                        @endforeach
                        <tr><td>Mã xe</td><td>{{ $car->sku }}</td></tr>
                    </table>
                </div>

                <div class="tab-panel" id="tab-desc">
                    <p class="desc">{{ $car->description }}</p>
                </div>

                <div class="tab-panel" id="tab-loan">
                    <div data-loan data-price="{{ $car->effectivePrice() }}">
                        <p class="muted">Ước tính khoản vay theo phương pháp trả góp đều hằng tháng. Kết quả chỉ mang tính tham khảo.</p>
                        <div class="field">
                            <label>Tỷ lệ vay</label>
                            <div class="range-row"><input type="range" name="loan_pct" min="10" max="80" step="5" value="70"><output data-out="pct"></output></div>
                        </div>
                        <div class="field">
                            <label>Thời hạn vay</label>
                            <div class="range-row"><input type="range" name="loan_months" min="12" max="96" step="12" value="60"><output data-out="months"></output></div>
                        </div>
                        <div class="field" style="max-width:220px">
                            <label for="loan_rate">Lãi suất (%/năm)</label>
                            <input class="input" id="loan_rate" type="number" name="loan_rate" min="0" max="30" step="0.1" value="{{ config('autoshop.loan_rate') }}">
                        </div>
                        <div class="loan-result">
                            <div><small>Trả trước</small><b data-out="upfront"></b></div>
                            <div><small>Số tiền vay</small><b data-out="principal"></b></div>
                            <div><small>Trả mỗi tháng</small><b data-out="monthly"></b></div>
                        </div>
                        <p class="muted" style="margin-top:10px">Tổng tiền lãi dự kiến: <b data-out="interest"></b></p>
                    </div>
                </div>

                <div class="tab-panel" id="tab-drive">
                    @if($errors->any())
                        <div class="alert alert-bad"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form method="post" action="{{ route('testdrive.store', $car) }}">
                        @csrf
                        <div class="grid-2">
                            <div class="field"><label for="td-name">Họ tên</label><input class="input" id="td-name" name="name" required value="{{ old('name', auth()->user()?->name) }}"></div>
                            <div class="field"><label for="td-phone">Số điện thoại</label><input class="input" id="td-phone" name="phone" required value="{{ old('phone', auth()->user()?->phone) }}"></div>
                        </div>
                        <div class="field"><label for="td-showroom">Showroom</label>
                            <select class="input" id="td-showroom" name="showroom">@foreach(config('autoshop.showrooms') as $s)<option @selected(old('showroom') === $s)>{{ $s }}</option>@endforeach</select>
                        </div>
                        <div class="grid-2">
                            <div class="field"><label for="td-date">Ngày lái thử</label><input class="input" id="td-date" type="date" name="preferred_date" required min="{{ now()->toDateString() }}" value="{{ old('preferred_date', now()->addDay()->toDateString()) }}"></div>
                            <div class="field"><label for="td-time">Khung giờ</label>
                                <select class="input" id="td-time" name="preferred_time">@foreach(['08:30', '09:30', '10:30', '13:30', '14:30', '15:30', '16:30'] as $t)<option @selected(old('preferred_time') === $t)>{{ $t }}</option>@endforeach</select>
                            </div>
                        </div>
                        <div class="field"><label for="td-note">Ghi chú</label><textarea class="input" id="td-note" name="note" placeholder="Ví dụ: muốn thử trên cao tốc, cần ghế trẻ em…">{{ old('note') }}</textarea></div>
                        <button class="btn btn-dark" type="submit"><x-icon name="key"/> Đăng ký lái thử miễn phí</button>
                    </form>
                </div>
            </div>
        </div>

        <aside class="card buybox">
            <span class="eyebrow">{{ $car->brand?->name }} · {{ $car->category->name }}</span>
            <h1 style="font-size:28px">{{ $car->name }}</h1>
            <p class="muted">{{ $car->short_description }}</p>

            @if($car->discountPercent())
                <div class="price-old">Giá niêm yết {{ vnd($car->price) }}</div>
            @endif
            <div style="display:flex;align-items:center;gap:10px">
                <div class="price">{{ vnd($car->effectivePrice()) }}</div>
                @if($car->discountPercent())<span class="badge badge-accent">-{{ $car->discountPercent() }}%</span>@endif
            </div>

            <div class="kv">
                <div><small>Đời xe</small><b>{{ $car->year }}</b></div>
                <div><small>Tình trạng</small><b>{{ $car->condition === 'new' ? 'Mới 100%' : number_format($car->mileage, 0, ',', '.').' km' }}</b></div>
                <div><small>Tại đại lý</small><b>{{ $car->stock > 0 ? $car->stock.' chiếc' : 'Hết xe' }}</b></div>
            </div>

            <div class="alert alert-info" style="margin:0 0 16px">
                Đặt cọc chỉ từ <b>{{ vnd($deposit) }}</b> ({{ config('autoshop.deposit_percent') }}% giá lăn bánh ước tính). Hoàn cọc 100% nếu không đủ xe giao.
            </div>

            @if($car->isPurchasable())
                <form method="post" action="{{ route('cart.add', $car) }}">
                    @csrf
                    <div class="qty" style="margin-bottom:12px">
                        <label for="qty" style="margin:0">Số lượng</label>
                        <input class="input" id="qty" type="number" name="quantity" min="1" max="{{ min($car->stock, config('autoshop.max_qty_per_car')) }}" value="1">
                    </div>
                    <div style="display:grid;gap:8px">
                        <button class="btn btn-block" type="submit" name="buy_now" value="1">Đặt cọc ngay</button>
                        <button class="btn btn-ghost btn-block" type="submit"><x-icon name="cart"/> Thêm vào giỏ xe</button>
                    </div>
                </form>
            @else
                <button class="btn btn-block" disabled>Tạm hết xe – liên hệ để đặt trước</button>
            @endif

            <div style="display:flex;gap:8px;margin-top:10px">
                <form method="post" action="{{ route('cars.compare.toggle', $car) }}" style="flex:1">@csrf
                    <button class="btn btn-ghost btn-block btn-sm" type="submit"><x-icon name="compare"/> {{ $inCompare ? 'Bỏ so sánh' : 'So sánh' }}</button>
                </form>
                @auth
                    <form method="post" action="{{ route('account.wishlist.toggle', $car) }}" style="flex:1">@csrf
                        <button class="btn btn-ghost btn-block btn-sm" type="submit"><x-icon name="heart"/> {{ $inWishlist ? 'Bỏ yêu thích' : 'Yêu thích' }}</button>
                    </form>
                @endauth
            </div>
            <hr>
            <small><x-icon name="phone" style="display:inline;width:16px;vertical-align:-3px"/> Tư vấn trực tiếp: <b>{{ config('autoshop.hotline') }}</b></small>
        </aside>
    </div>

    @if($related->isNotEmpty())
        <section class="section">
            <div class="section-head"><div><span class="eyebrow">Có thể bạn quan tâm</span><h2>Xe tương tự</h2></div></div>
            <div class="car-grid cols-4">@foreach($related as $r)<x-car-card :car="$r"/>@endforeach</div>
        </section>
    @endif
</div>
@endsection
