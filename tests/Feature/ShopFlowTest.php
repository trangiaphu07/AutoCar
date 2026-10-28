<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function checkoutData(array $extra = []): array
    {
        return $extra + [
            'customer_name' => 'Lê Văn Test',
            'customer_phone' => '0909123456',
            'customer_email' => 'test@example.com',
            'address' => '1 Lê Lợi, Q.1, TP.HCM',
            'showroom' => config('autoshop.showrooms')[0],
            'payment_method' => 'showroom',
            'agree' => '1',
        ];
    }

    public function test_public_pages_load(): void
    {
        $car = Car::published()->first();
        foreach (['/', '/xe', '/dong-xe/xe-dien', '/xe/'.$car->slug, '/gio-xe', '/so-sanh', '/dang-nhap', '/dang-ky'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_dynamic_attribute_filter(): void
    {
        $this->get('/dong-xe/suv-crossover?attr[so_cho][]=7 chỗ')
            ->assertOk()
            ->assertSee('Honda CR-V e:HEV RS')
            ->assertDontSee('Mazda CX-5 2.0 Premium');
    }

    public function test_pagination_keeps_filters(): void
    {
        $this->get('/xe?condition=new&per_page=9')
            ->assertOk()
            ->assertSee('condition=new', false);
    }

    public function test_cannot_add_more_than_stock(): void
    {
        $car = Car::where('sku', 'XE1007')->first(); // còn 1 chiếc
        $this->post("/gio-xe/{$car->id}", ['quantity' => 2])->assertSessionHas('error');
        $this->assertSame([], session('cart', []));
    }

    public function test_place_order_decrements_stock_and_cancel_restocks(): void
    {
        $user = User::where('email', 'khach@autoshop.test')->first();
        $car = Car::where('sku', 'XE1002')->first();
        $before = $car->stock;

        $this->actingAs($user)->post("/gio-xe/{$car->id}", ['quantity' => 2]);
        $this->post('/dat-mua', $this->checkoutData())->assertRedirect();

        $order = Order::latest('id')->first();
        $this->assertSame('pending', $order->status);
        $this->assertSame($car->effectivePrice() * 2, $order->subtotal);
        $this->assertSame($before - 2, $car->fresh()->stock);

        $this->post("/tai-khoan/don-hang/{$order->code}/huy")->assertSessionHas('success');
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame($before, $car->fresh()->stock);
    }

    public function test_mock_vnpay_success_marks_deposited(): void
    {
        $car = Car::where('sku', 'XE1004')->first();
        $this->post("/gio-xe/{$car->id}");
        $res = $this->post('/dat-mua', $this->checkoutData(['payment_method' => 'vnpay']));

        $order = Order::latest('id')->first();
        $tx = $order->transactions()->first();
        $res->assertRedirect(route('payment.mock', $tx->transaction_code));

        $this->post(route('payment.mock.submit', $tx->transaction_code), ['result' => 'success'])
            ->assertRedirect(route('orders.result', $order->code));

        $order->refresh();
        $this->assertSame('deposited', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
    }

    public function test_vnpay_ipn_signature_amount_and_idempotency(): void
    {
        config(['autoshop.vnpay.tmn_code' => 'TEST', 'autoshop.vnpay.hash_secret' => 'SECRET']);

        $car = Car::where('sku', 'XE1005')->first();
        $this->post("/gio-xe/{$car->id}");
        $this->post('/dat-mua', $this->checkoutData(['payment_method' => 'vnpay']));
        $tx = Order::latest('id')->first()->transactions()->first();

        $sign = function (array $p) {
            ksort($p);
            $p['vnp_SecureHash'] = hash_hmac('sha512', http_build_query($p, '', '&', PHP_QUERY_RFC1738), 'SECRET');

            return $p;
        };
        $base = ['vnp_TxnRef' => $tx->transaction_code, 'vnp_ResponseCode' => '00', 'vnp_TransactionStatus' => '00', 'vnp_TransactionNo' => '14123456'];

        $this->get('/payment/vnpay/ipn?'.http_build_query($base + ['vnp_Amount' => $tx->amount * 100, 'vnp_SecureHash' => 'sai']))->assertJson(['RspCode' => '97']);
        $this->get('/payment/vnpay/ipn?'.http_build_query($sign($base + ['vnp_Amount' => 100])))->assertJson(['RspCode' => '04']);
        $this->get('/payment/vnpay/ipn?'.http_build_query($sign($base + ['vnp_Amount' => $tx->amount * 100])))->assertJson(['RspCode' => '00']);
        $this->get('/payment/vnpay/ipn?'.http_build_query($sign($base + ['vnp_Amount' => $tx->amount * 100])))->assertJson(['RspCode' => '02']);

        $this->assertSame('deposited', $tx->order->fresh()->payment_status);
    }

    public function test_expired_vnpay_orders_are_cancelled(): void
    {
        $car = Car::where('sku', 'XE1012')->first();
        $before = $car->stock;
        $this->post("/gio-xe/{$car->id}");
        $this->post('/dat-mua', $this->checkoutData(['payment_method' => 'vnpay']));
        $order = Order::latest('id')->first();
        $order->forceFill(['created_at' => now()->subMinutes(20)])->saveQuietly();

        $this->artisan('orders:cancel-expired')->assertSuccessful();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame($before, $car->fresh()->stock);
        // Đơn đã cọc không bị ảnh hưởng
        $this->assertSame(0, Order::where('payment_status', 'deposited')->where('status', 'cancelled')->count());
    }

    public function test_admin_area_is_protected(): void
    {
        $this->get('/admin')->assertRedirect('/dang-nhap');
        $this->actingAs(User::where('role', 'customer')->first())->get('/admin/cars')->assertForbidden();
    }

    public function test_admin_pages_load(): void
    {
        $admin = User::where('role', 'admin')->first();
        $car = Car::first();
        foreach (['/admin', '/admin/users', '/admin/users/create', '/admin/cars', '/admin/cars/create', "/admin/cars/{$car->id}/edit", '/admin/catalog', '/admin/orders', '/admin/orders/'.Order::first()->id, '/admin/test-drives', '/admin/import-export', '/admin/export'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_publish_is_blocked_when_required_specs_missing(): void
    {
        $admin = User::where('role', 'admin')->first();
        $cat = \App\Models\Category::where('slug', 'sedan')->first();

        $this->actingAs($admin)->post('/admin/cars', [
            'category_id' => $cat->id, 'sku' => 'XE9999', 'name' => 'Xe thiếu thông số', 'year' => 2025,
            'condition' => 'new', 'price' => 500000000, 'stock' => 1, 'status' => 'published', 'attr' => [],
        ])->assertSessionHasErrors();

        $this->assertDatabaseMissing('cars', ['sku' => 'XE9999']);
    }

    public function test_bank_transfer_shows_vietqr_and_admin_confirms(): void
    {
        $car = Car::where('sku', 'XE1004')->first();
        $this->post("/gio-xe/{$car->id}");
        $this->post('/dat-mua', $this->checkoutData(['payment_method' => 'bank_transfer']))->assertRedirect();

        $order = Order::latest('id')->first();
        $tx = $order->transactions()->first();
        $this->assertSame('bank_transfer', $tx->gateway);

        $this->get(route('orders.result', $order->code))
            ->assertOk()
            ->assertSee('img.vietqr.io', false)
            ->assertSee($tx->transaction_code);

        // Khách không tự xác nhận được
        $this->post("/admin/orders/{$order->id}/confirm-transfer")->assertRedirect();
        $this->assertSame('unpaid', $order->fresh()->payment_status);

        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get("/admin/orders/{$order->id}")->assertOk()->assertSee('Xác nhận đã nhận tiền');
        $this->post("/admin/orders/{$order->id}/confirm-transfer", ['bank_ref' => 'FT123'])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('deposited', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('FT123', $tx->fresh()->gateway_transaction_no);

        // Bấm lần hai không ghi nhận thêm
        $this->post("/admin/orders/{$order->id}/confirm-transfer")->assertSessionHas('error');
    }

    public function test_order_status_cannot_skip_steps(): void
    {
        $admin = User::where('role', 'admin')->first();
        $order = Order::where('status', 'pending')->first();

        $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", ['status' => 'completed'])->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);

        $this->patch("/admin/orders/{$order->id}/status", ['status' => 'confirmed'])->assertSessionHas('success');
        $this->patch("/admin/orders/{$order->id}/status", ['status' => 'delivering'])->assertSessionHas('success');
        $this->assertSame('delivering', $order->fresh()->status);
    }

    public function test_csv_import_only_imports_valid_rows(): void
    {
        $admin = User::where('role', 'admin')->first();
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('cars.csv', file_get_contents(base_path('database/sample-import.csv')));

        $this->actingAs($admin)->post('/admin/import', ['file' => $file])->assertSessionHas('import_errors', fn ($e) => count($e) === 2);

        $this->assertDatabaseHas('cars', ['sku' => 'XE2001', 'status' => 'published']);
        $this->assertDatabaseHas('cars', ['sku' => 'XE2002']);
        $this->assertDatabaseMissing('cars', ['sku' => 'XE2004']);
    }

    public function test_register_rejects_vietnamese_password(): void
    {
        $this->post('/dang-ky', [
            'name' => 'Người Mới', 'email' => 'moi@example.com',
            'password' => 'mậtkhẩu123', 'password_confirmation' => 'mậtkhẩu123',
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'moi@example.com']);
    }

    public function test_test_drive_booking(): void
    {
        $car = Car::published()->first();
        $this->post("/lai-thu/{$car->id}", [
            'name' => 'Khách Lái Thử', 'phone' => '0911222333', 'showroom' => config('autoshop.showrooms')[1],
            'preferred_date' => now()->addDays(3)->toDateString(), 'preferred_time' => '09:30',
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('test_drives', ['phone' => '0911222333', 'status' => 'new']);
    }

    public function test_customer_can_update_profile_and_password(): void
    {
        $user = User::where('role', 'customer')->first();

        $this->actingAs($user)->get('/tai-khoan/thong-tin')->assertOk();

        $this->put('/tai-khoan/thong-tin', [
            'name' => 'Nguyễn Khách Mới',
            'email' => 'khachmoi@autoshop.test',
            'phone' => '0988776655',
        ])->assertSessionHas('success');

        $this->assertSame('Nguyễn Khách Mới', $user->fresh()->name);
        $this->assertSame('khachmoi@autoshop.test', $user->fresh()->email);

        $this->put('/tai-khoan/doi-mat-khau', [
            'current_password' => 'password',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertSessionHas('success');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Thêm user mới
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Nhân viên Mới',
            'email' => 'nhanvien@autoshop.test',
            'phone' => '0912345678',
            'role' => 'customer',
            'password' => 'password123',
        ])->assertRedirect(route('admin.users.index'));

        $created = User::where('email', 'nhanvien@autoshop.test')->first();
        $this->assertNotNull($created);

        // Sửa quyền
        $this->put("/admin/users/{$created->id}", [
            'name' => 'Nhân viên Đã sửa',
            'email' => 'nhanvien@autoshop.test',
            'phone' => '0912345678',
            'role' => 'admin',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame('admin', $created->fresh()->role);

        // Xóa user
        $this->delete("/admin/users/{$created->id}")->assertSessionHas('success');
        $this->assertNull(User::where('email', 'nhanvien@autoshop.test')->first());
    }
}
