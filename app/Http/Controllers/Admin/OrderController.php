<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::withCount('items')->latest('id');

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('code', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%")
                ->orWhere('customer_phone', 'like', "%{$q}%"));
        }
        if ($s = $request->query('status')) {
            $query->where('status', $s);
        }
        if ($p = $request->query('payment_status')) {
            $query->where('payment_status', $p);
        }

        return view('admin.orders.index', ['orders' => $query->paginate(15)->withQueryString()]);
    }

    public function show(Order $order)
    {
        $order->load('items.car', 'transactions', 'user');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Order::STATUSES))]]);

        try {
            $order->transitionTo($data['status']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật trạng thái đơn: '.$order->statusLabel().'.');
    }

    public function confirmTransfer(Request $request, Order $order, PaymentService $payments)
    {
        $data = $request->validate(['bank_ref' => ['nullable', 'string', 'max:100']]);

        try {
            $payments->confirmTransfer($order, $request->user(), $data['bank_ref'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã xác nhận nhận tiền cọc '.vnd($order->deposit_amount).' cho đơn '.$order->code.'.');
    }
}
