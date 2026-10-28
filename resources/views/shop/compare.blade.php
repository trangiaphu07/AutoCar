@extends('layouts.app')

@section('title', 'So sánh xe – AutoShop')

@section('content')
<div class="container" style="padding:24px 16px 48px">
    <h1>So sánh xe</h1>
    @if($cars->isEmpty())
        <div class="card empty">
            <h3>Chưa có xe nào để so sánh</h3>
            <p>Bấm nút <b>So sánh</b> trên thẻ xe để thêm tối đa 3 xe.</p>
            <a class="btn" href="{{ route('cars.index') }}">Xem danh sách xe</a>
        </div>
    @else
        <div class="card table-wrap table-responsive">
            <table class="table compare-table">
                <tr>
                    <th></th>
                    @foreach($cars as $car)
                        <td>
                            <a href="{{ route('cars.show', $car->slug) }}">
                                @if($car->imageUrl())
                                    <img src="{{ $car->imageUrl() }}" alt="{{ $car->name }}" style="max-width:220px;width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:8px;margin:0 auto 10px;display:block">
                                @else
                                    <x-car-art :type="body_type($car->category->slug)" :color="$car->color_hex" style="max-width:220px"/>
                                @endif
                            </a>
                            <b>{{ $car->name }}</b>
                            <div class="price" style="margin:4px 0 10px">{{ vnd($car->effectivePrice()) }}</div>
                            <form method="post" action="{{ route('cars.compare.toggle', $car) }}">@csrf<button class="btn btn-ghost btn-sm" type="submit">Bỏ khỏi so sánh</button></form>
                        </td>
                    @endforeach
                </tr>
                <tr><th>Hãng</th>@foreach($cars as $car)<td>{{ $car->brand?->name }}</td>@endforeach</tr>
                <tr><th>Dòng xe</th>@foreach($cars as $car)<td>{{ $car->category->name }}</td>@endforeach</tr>
                <tr><th>Năm sản xuất</th>@foreach($cars as $car)<td>{{ $car->year }}</td>@endforeach</tr>
                <tr><th>Tình trạng</th>@foreach($cars as $car)<td>{{ \App\Models\Car::CONDITIONS[$car->condition] }}@if($car->condition === 'used') ({{ number_format($car->mileage, 0, ',', '.') }} km)@endif</td>@endforeach</tr>
                <tr><th>Màu sắc</th>@foreach($cars as $car)<td>{{ $car->color }}</td>@endforeach</tr>
                @foreach($specNames as $label)
                    <tr>
                        <th>{{ $label }}</th>
                        @foreach($cars as $car)
                            <td>{{ $car->attributeValues->first(fn ($v) => $v->attribute->label() === $label)?->value ?? '—' }}</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr><th></th>@foreach($cars as $car)<td><a class="btn btn-sm" href="{{ route('cars.show', $car->slug) }}">Xem chi tiết</a></td>@endforeach</tr>
            </table>
        </div>
    @endif
</div>
@endsection
