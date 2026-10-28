<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') – AutoShop</title>
    <script>try{var t=localStorage.getItem('autoshop-theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="container" style="max-width:560px;text-align:center;padding:80px 16px">
    <x-car-art type="sedan" color="#9aa3ad" style="max-width:260px;margin:0 auto;opacity:.7"/>
    <h1 style="font-size:56px;margin:10px 0 0;color:var(--accent)">@yield('code')</h1>
    <h2>@yield('message')</h2>
    <p class="muted">@yield('hint')</p>
    <a class="btn" href="{{ url('/') }}">Về trang chủ</a>
</div>
</body>
</html>
