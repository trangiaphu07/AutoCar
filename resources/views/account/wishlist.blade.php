@extends('layouts.app')

@section('title', 'Xe yêu thích – AutoShop')

@section('content')
<div class="container" style="padding-bottom:48px">
    @include('account._nav')
    <h1>Xe yêu thích</h1>
    @if($cars->isEmpty())
        <div class="card empty"><h3>Chưa có xe yêu thích</h3><p>Bấm “Yêu thích” ở trang chi tiết xe để lưu lại.</p></div>
    @else
        <div class="car-grid cols-4">@foreach($cars as $car)<x-car-card :car="$car"/>@endforeach</div>
    @endif
</div>
@endsection
