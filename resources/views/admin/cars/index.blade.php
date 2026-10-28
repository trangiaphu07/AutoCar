@extends('layouts.admin')

@section('title', 'Quản lý xe')

@section('content')
<div class="page-head">
    <h1>Quản lý xe</h1>
    <a class="btn" href="{{ route('admin.cars.create') }}">+ Thêm xe</a>
</div>

<form class="filters-row" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Tìm tên hoặc mã xe">
    <select class="input" name="category" data-autosubmit>
        <option value="">Tất cả dòng xe</option>
        @foreach($categories as $c)<option value="{{ $c->id }}" @selected((int) request('category') === $c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <select class="input" name="status" data-autosubmit>
        <option value="">Mọi trạng thái</option>
        @foreach(\App\Models\Car::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
    </select>
    <button class="btn btn-ghost" type="submit">Lọc</button>
</form>

<div class="card table-wrap table-responsive">
    <table class="table table-hover align-middle">
        <tr><th style="width:110px"></th><th>Xe</th><th>Dòng / hãng</th><th class="num">Giá bán</th><th class="num">Kho</th><th>Hoàn thiện</th><th>Trạng thái</th><th></th></tr>
        @forelse($cars as $car)
            <tr>
                <td>
                    @if($car->imageUrl())
                        <img src="{{ $car->imageUrl() }}" alt="{{ $car->name }}" style="width:80px;aspect-ratio:16/10;object-fit:cover;border-radius:6px;display:block">
                    @else
                        <x-car-art :type="body_type($car->category?->slug)" :color="$car->color_hex"/>
                    @endif
                </td>
                <td><b>{{ $car->name }}</b><br><small>{{ $car->sku }} · {{ $car->year }} · {{ $car->condition === 'used' ? 'Xe cũ' : 'Xe mới' }}</small></td>
                <td>{{ $car->category?->name }}<br><small>{{ $car->brand?->name }}</small></td>
                <td class="num">{{ vnd($car->effectivePrice()) }}@if($car->discountPercent())<br><small class="price-old">{{ vnd($car->price) }}</small>@endif</td>
                <td class="num"><span class="badge {{ $car->stock === 0 ? 'badge-bad' : ($car->stock <= 2 ? 'badge-warn' : '') }}">{{ $car->stock }}</span></td>
                <td style="min-width:120px"><div class="meter {{ $car->completeness < 60 ? 'low' : ($car->completeness < 100 ? 'mid' : '') }}"><i style="width:{{ $car->completeness }}%"></i></div><small>{{ $car->completeness }}%</small></td>
                <td><span class="badge {{ ['draft' => '', 'review' => 'badge-warn', 'published' => 'badge-ok'][$car->status] }}">{{ \App\Models\Car::STATUSES[$car->status] }}</span></td>
                <td style="white-space:nowrap">
                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.cars.edit', $car) }}">Sửa</a>
                    <form class="inline-form" method="post" action="{{ route('admin.cars.destroy', $car) }}" data-confirm="Xóa xe {{ $car->name }}?">@csrf @method('delete')<button class="btn btn-ghost btn-sm" type="submit">Xóa</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">Không có xe nào.</td></tr>
        @endforelse
    </table>
</div>
{{ $cars->links() }}
@endsection
