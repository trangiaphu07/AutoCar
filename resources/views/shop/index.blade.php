@extends('layouts.app')

@section('title', ($category?->name ?? 'Tất cả xe').' – AutoShop')

@section('content')
<div class="container">
    <div class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> / <a href="{{ route('cars.index') }}">Xe</a>@if($category) / <span>{{ $category->name }}</span>@endif</div>

    <div class="shop-layout">
        <aside>
            <button class="btn btn-ghost filter-toggle btn-block" type="button" data-filter-toggle style="margin-bottom:12px"><x-icon name="filter"/> Bộ lọc</button>
            <form class="card filters" method="get" action="{{ $category ? route('cars.category', $category) : route('cars.index') }}">
                @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">

                @unless($category)
                    <div class="filter-group">
                        <h4>Dòng xe</h4>
                        @foreach($categories as $c)
                            <a class="check" href="{{ route('cars.category', $c) }}" style="margin-bottom:6px">› {{ $c->name }}</a>
                        @endforeach
                    </div>
                @endunless

                <div class="filter-group">
                    <h4>Hãng xe</h4>
                    @foreach($brands as $b)
                        <label class="check"><input type="checkbox" name="brand[]" value="{{ $b->slug }}" @checked(in_array($b->slug, (array) request('brand')))> {{ $b->name }}</label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h4>Khoảng giá</h4>
                    <label class="check"><input type="radio" name="price" value="" @checked(! request('price'))> Mọi mức giá</label>
                    @foreach($priceRanges as $k => $r)
                        <label class="check"><input type="radio" name="price" value="{{ $k }}" @checked(request('price') === $k)> {{ $r[0] }}</label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h4>Tình trạng & đời xe</h4>
                    <select class="input" name="condition" style="margin-bottom:8px">
                        <option value="">Mới & đã qua sử dụng</option>
                        @foreach(\App\Models\Car::CONDITIONS as $k => $v)<option value="{{ $k }}" @selected(request('condition') === $k)>{{ $v }}</option>@endforeach
                    </select>
                    <select class="input" name="year_from">
                        <option value="">Mọi năm sản xuất</option>
                        @foreach([2025, 2024, 2023, 2022, 2020] as $y)<option value="{{ $y }}" @selected((int) request('year_from') === $y)>Từ {{ $y }} trở lên</option>@endforeach
                    </select>
                </div>

                {{-- Bộ lọc thông số động theo dòng xe (EAV) --}}
                @foreach($filterAttributes as $attr)
                    @if($attr->available->isNotEmpty())
                        <div class="filter-group">
                            <h4>{{ $attr->label() }}</h4>
                            @foreach($attr->available as $opt)
                                <label class="check">
                                    <input type="checkbox" name="attr[{{ $attr->code }}][]" value="{{ $opt->value }}" @checked(in_array($opt->value, (array) data_get(request('attr'), $attr->code)))>
                                    {{ $opt->value }}{{ $attr->type === 'number' && $attr->unit ? ' '.$attr->unit : '' }} <span class="c">{{ $opt->total }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                @endforeach

                <div class="filter-group" style="display:flex;gap:8px">
                    <button class="btn" type="submit" style="flex:1">Áp dụng</button>
                    <a class="btn btn-ghost" href="{{ $category ? route('cars.category', $category) : route('cars.index') }}">Xóa lọc</a>
                </div>
            </form>
        </aside>

        <section>
            <div class="toolbar">
                <div>
                    <h1 style="margin:0;font-size:26px">{{ $category?->name ?? (request('q') ? 'Kết quả cho “'.request('q').'”' : 'Tất cả xe') }}</h1>
                    <small>{{ $category?->description ?? 'Tìm thấy' }} · {{ $cars->total() }} xe phù hợp</small>
                </div>
                <form method="get">
                    {{-- giữ nguyên bộ lọc hiện tại khi đổi cách sắp xếp --}}
                    @foreach(array_filter(explode('&', http_build_query(request()->except(['sort', 'per_page', 'page'])))) as $pair)
                        @php [$hk, $hv] = array_map('urldecode', array_pad(explode('=', $pair, 2), 2, '')); @endphp
                        <input type="hidden" name="{{ preg_replace('/\[\d+\]$/', '[]', $hk) }}" value="{{ $hv }}">
                    @endforeach
                    <select class="input" name="sort" data-autosubmit aria-label="Sắp xếp">
                        <option value="">Mới nhất</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá thấp → cao</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá cao → thấp</option>
                        <option value="year_desc" @selected(request('sort') === 'year_desc')>Đời xe mới nhất</option>
                        <option value="popular" @selected(request('sort') === 'popular')>Xem nhiều nhất</option>
                    </select>
                    <select class="input" name="per_page" data-autosubmit aria-label="Số xe mỗi trang">
                        @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected((int) request('per_page', $perPageOptions[0]) === $n)>{{ $n }} / trang</option>@endforeach
                    </select>
                </form>
            </div>

            @if($cars->isEmpty())
                <div class="card empty">
                    <x-car-art type="sedan" color="#9aa3ad" style="max-width:220px;margin:0 auto 10px;opacity:.6"/>
                    <h3>Không có xe phù hợp</h3>
                    <p>Hãy thử bỏ bớt điều kiện lọc hoặc tìm với từ khóa khác.</p>
                </div>
            @else
                <div class="car-grid">
                    @foreach($cars as $car)<x-car-card :car="$car"/>@endforeach
                </div>
                {{ $cars->links() }}
            @endif
        </section>
    </div>
</div>
@endsection
