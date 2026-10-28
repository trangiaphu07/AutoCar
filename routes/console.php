<?php

use App\Services\OrderService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('orders:cancel-expired', function (OrderService $orders) {
    $count = $orders->cancelExpired();
    $this->info("Đã hủy {$count} đơn VNPay quá hạn chưa đặt cọc.");
})->purpose('Hủy đơn VNPay chưa đặt cọc quá hạn và trả xe về kho');

Schedule::command('orders:cancel-expired')->everyFiveMinutes();
