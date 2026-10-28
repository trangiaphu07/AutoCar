<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orders,
        private PaymentService $payments,
    ) {}

    public function form()
    {
        $summary = $this->cart->summary();

        if ($summary['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ xe đang trống, hãy chọn xe trước khi đặt mua.');
        }

        return view('checkout.form', [
            'summary' => $summary,
            'user' => auth()->user(),
            'showrooms' => config('autoshop.showrooms'),
            'isMock' => $this->payments->isMock(),
        ]);
    }

    public function place(CheckoutRequest $request)
    {
        $data = $request->validated();

        try {
            $order = $this->orders->place($data, auth()->id());
        } catch (RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        session()->push('my_orders', $order->code);

        if ($order->payment_method === 'vnpay') {
            $tx = $order->transactions()->first();

            return redirect()->away($this->payments->paymentUrl($tx, $request->ip()));
        }

        if ($order->payment_method === 'bank_transfer') {
            return redirect()->route('orders.result', $order->code)
                ->with('success', 'Đặt xe thành công! Vui lòng quét mã QR để chuyển khoản tiền cọc.');
        }

        return redirect()->route('orders.result', $order->code)
            ->with('success', 'Đặt xe thành công! Nhân viên AutoShop sẽ gọi cho bạn trong 30 phút để hẹn lịch đặt cọc.');
    }

    public function result(Order $order)
    {
        $this->authorizeView($order);

        $order->load('items.car', 'transactions');

        $transfer = $order->awaitingTransfer() ? $this->payments->pendingTransfer($order) : null;

        return view('checkout.result', [
            'order' => $order,
            'transfer' => $transfer,
            'qrUrl' => $transfer ? $this->payments->vietQrUrl($transfer) : null,
            'bank' => config('autoshop.bank'),
        ]);
    }

    public function retry(Request $request, Order $order)
    {
        $this->authorizeView($order);

        if ($order->payment_method !== 'vnpay' || ! $order->canBeCancelledByCustomer()) {
            return back()->with('error', 'Đơn này không thể thanh toán lại.');
        }

        $tx = $this->payments->retry($order);

        return redirect()->away($this->payments->paymentUrl($tx, $request->ip()));
    }

    /** Chỉ chủ đơn (tài khoản hoặc phiên vừa đặt) hoặc admin mới xem được đơn. */
    private function authorizeView(Order $order): void
    {
        $user = auth()->user();
        $allowed = in_array($order->code, session('my_orders', []), true)
            || ($user && ($user->isAdmin() || $order->user_id === $user->id));

        abort_unless($allowed, 403, 'Bạn không có quyền xem đơn hàng này.');
    }
}
