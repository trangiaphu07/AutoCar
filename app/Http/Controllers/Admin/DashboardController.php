<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\TestDrive;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $valid = Order::where('status', '!=', 'cancelled');

        // Doanh số (giá trị xe) của các đơn đã cọc/đã thanh toán, chưa hủy
        $revenue = (clone $valid)->whereIn('payment_status', ['deposited', 'paid'])->sum('subtotal');
        // Tiền thực thu qua các giao dịch thành công
        $collected = PaymentTransaction::where('status', 'success')
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))->sum('amount');

        $byStatus = Order::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        $gateways = PaymentTransaction::select('gateway', 'status', DB::raw('count(*) as total'))
            ->groupBy('gateway', 'status')->get()->groupBy('gateway')
            ->map(function ($rows) {
                $all = $rows->sum('total');
                $ok = $rows->where('status', 'success')->sum('total');

                return ['all' => $all, 'ok' => $ok, 'rate' => $all ? round($ok / $all * 100) : 0];
            });

        $topCars = OrderItem::select('name', DB::raw('sum(quantity) as qty'), DB::raw('sum(price * quantity) as amount'))
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->groupBy('name')->orderByDesc('qty')->take(5)->get();

        // Doanh số 6 tháng gần nhất (tính bằng PHP để chạy được cả MySQL lẫn SQLite)
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $orders6 = (clone $valid)->whereIn('payment_status', ['deposited', 'paid'])
            ->where('created_at', '>=', $months->first())->get(['subtotal', 'created_at']);
        $monthly = $months->map(fn ($m) => [
            'label' => 'T'.$m->month,
            'value' => $orders6->filter(fn ($o) => $o->created_at->isSameMonth($m))->sum('subtotal'),
        ]);

        return view('admin.dashboard', [
            'revenue' => $revenue,
            'collected' => $collected,
            'orderCount' => Order::count(),
            'carCount' => Car::published()->count(),
            'stockTotal' => Car::published()->sum('stock'),
            'byStatus' => $byStatus,
            'gateways' => $gateways,
            'topCars' => $topCars,
            'monthly' => $monthly,
            'lowStock' => Car::published()->where('stock', '<=', 2)->orderBy('stock')->take(8)->get(),
            'incomplete' => Car::where('completeness', '<', 100)->orderBy('completeness')->take(8)->get(),
            'newTestDrives' => TestDrive::with('car')->where('status', 'new')->orderBy('preferred_date')->take(5)->get(),
            'recentOrders' => Order::latest('id')->take(6)->get(),
        ]);
    }
}
