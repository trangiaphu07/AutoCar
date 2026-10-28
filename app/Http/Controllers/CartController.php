<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Services\CartService;
use Illuminate\Http\Request;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('cart.index', ['summary' => $this->cart->summary()]);
    }

    public function add(Request $request, Car $car)
    {
        $qty = max(1, (int) $request->input('quantity', 1));

        try {
            $this->cart->add($car, $qty);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.form');
        }

        return back()->with('success', "Đã thêm “{$car->name}” vào giỏ xe.");
    }

    public function update(Request $request, Car $car)
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']]);

        try {
            $this->cart->update($car, (int) $request->input('quantity'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật giỏ xe.');
    }

    public function remove(Car $car)
    {
        $this->cart->remove($car->id);

        return back()->with('success', "Đã xóa “{$car->name}” khỏi giỏ xe.");
    }
}
