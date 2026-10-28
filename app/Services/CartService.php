<?php

namespace App\Services;

use App\Models\Car;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Giỏ xe lưu trong session dưới dạng [car_id => số lượng].
 * Giá luôn được tính lại từ cơ sở dữ liệu, không tin dữ liệu từ trình duyệt.
 */
class CartService
{
    private const KEY = 'cart';

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function add(Car $car, int $qty = 1): void
    {
        if (! $car->isPurchasable()) {
            throw new RuntimeException('Mẫu xe này hiện không còn mở bán.');
        }

        $cart = $this->raw();
        $newQty = ($cart[$car->id] ?? 0) + max(1, $qty);
        $this->guard($car, $newQty);
        $cart[$car->id] = $newQty;
        session([self::KEY => $cart]);
    }

    public function update(Car $car, int $qty): void
    {
        $cart = $this->raw();
        if ($qty <= 0) {
            unset($cart[$car->id]);
        } else {
            $this->guard($car, $qty);
            $cart[$car->id] = $qty;
        }
        session([self::KEY => $cart]);
    }

    public function remove(int $carId): void
    {
        $cart = $this->raw();
        unset($cart[$carId]);
        session([self::KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    private function guard(Car $car, int $qty): void
    {
        $max = (int) config('autoshop.max_qty_per_car');
        if ($qty > $max) {
            throw new RuntimeException("Mỗi đơn chỉ đặt tối đa {$max} chiếc cho một mẫu xe.");
        }
        if ($qty > $car->stock) {
            throw new RuntimeException("Mẫu xe này chỉ còn {$car->stock} chiếc tại đại lý.");
        }
    }

    /**
     * Tổng hợp giỏ: các dòng xe, tạm tính, chi phí lăn bánh ước tính, tổng, tiền cọc.
     */
    public function summary(): array
    {
        $raw = $this->raw();
        $cars = Car::published()->with(['brand', 'category'])->whereIn('id', array_keys($raw))->get()->keyBy('id');

        $items = new Collection;
        $subtotal = 0;
        $registration = 0;

        foreach ($raw as $id => $qty) {
            $car = $cars->get($id);
            if (! $car) {
                continue; // xe đã ngừng bán -> bỏ qua
            }
            $line = $car->effectivePrice() * $qty;
            $subtotal += $line;
            $registration += self::registrationFee($car, $qty);
            $items->push((object) ['car' => $car, 'quantity' => $qty, 'price' => $car->effectivePrice(), 'total' => $line]);
        }

        $total = $subtotal + $registration;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'registration' => $registration,
            'total' => $total,
            'deposit' => $items->isEmpty() ? 0 : self::depositFor($total),
        ];
    }

    public static function registrationFee(Car $car, int $qty): int
    {
        $rate = $car->condition === 'used'
            ? config('autoshop.registration_rate_used')
            : config('autoshop.registration_rate_new');

        return ((int) round($car->effectivePrice() * $rate / 100) + (int) config('autoshop.plate_fee')) * $qty;
    }

    /** Tiền cọc = % tổng giá trị, làm tròn nghìn đồng, không thấp hơn mức tối thiểu và không vượt tổng. */
    public static function depositFor(int $total): int
    {
        $deposit = (int) (round($total * config('autoshop.deposit_percent') / 100 / 1000) * 1000);

        return min($total, max((int) config('autoshop.min_deposit'), $deposit));
    }
}
