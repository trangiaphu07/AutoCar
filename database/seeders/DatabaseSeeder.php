<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Dữ liệu mẫu cho AutoShop. Giá xe là giá tham khảo để minh họa, không phải báo giá thật.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Quản trị AutoShop', 'email' => 'admin@autoshop.test',
            'phone' => '0909000001', 'password' => 'password', 'role' => 'admin',
        ]);
        $customer = User::create([
            'name' => 'Nguyễn Văn Khách', 'email' => 'khach@autoshop.test',
            'phone' => '0909000002', 'password' => 'password', 'role' => 'customer',
        ]);

        // Một số khách hàng ngẫu nhiên sinh bằng Factory (Faker vi_VN) để danh sách người dùng có dữ liệu
        if (class_exists(\Faker\Factory::class)) {
            User::factory()->count(6)->create(['role' => 'customer']);
        }

        /* ---------------- Thương hiệu ---------------- */
        $brands = [];
        foreach ([
            ['Toyota', 'Nhật Bản'], ['Honda', 'Nhật Bản'], ['Mazda', 'Nhật Bản'], ['Mitsubishi', 'Nhật Bản'],
            ['Hyundai', 'Hàn Quốc'], ['Kia', 'Hàn Quốc'], ['Ford', 'Mỹ'], ['VinFast', 'Việt Nam'],
            ['Mercedes-Benz', 'Đức'],
        ] as [$name, $country]) {
            $brands[$name] = Brand::create(['name' => $name, 'slug' => Str::slug($name), 'country' => $country]);
        }

        /* ---------------- Dòng xe + thông số (EAV) ---------------- */
        $fuel = ['Xăng', 'Dầu (Diesel)', 'Hybrid (Xăng – Điện)'];
        $gear = ['Số tự động', 'Số tự động vô cấp CVT', 'Số sàn'];

        $definitions = [
            'Sedan' => [
                'desc' => 'Xe 4 cửa, 5 chỗ, cốp tách rời – êm ái, tiết kiệm.',
                'attrs' => [
                    ['nhien_lieu', 'Nhiên liệu', 'select', null, $fuel, true, true],
                    ['hop_so', 'Hộp số', 'select', null, $gear, true, true],
                    ['dong_co', 'Động cơ', 'text', null, null, true, false],
                    ['cong_suat', 'Công suất', 'number', 'mã lực', null, false, false],
                    ['tieu_hao', 'Mức tiêu hao', 'number', 'L/100km', null, false, false],
                ],
            ],
            'SUV / Crossover' => [
                'desc' => 'Gầm cao, rộng rãi, phù hợp gia đình và đi đường xa.',
                'attrs' => [
                    ['so_cho', 'Số chỗ ngồi', 'select', null, ['5 chỗ', '7 chỗ'], true, true],
                    ['nhien_lieu', 'Nhiên liệu', 'select', null, $fuel, true, true],
                    ['hop_so', 'Hộp số', 'select', null, $gear, true, false],
                    ['dan_dong', 'Hệ dẫn động', 'select', null, ['Cầu trước (FWD)', 'Hai cầu toàn thời gian (AWD)', 'Hai cầu 4x4'], true, true],
                    ['dong_co', 'Động cơ', 'text', null, null, true, false],
                    ['cong_suat', 'Công suất', 'number', 'mã lực', null, false, false],
                ],
            ],
            'Bán tải' => [
                'desc' => 'Thùng hàng phía sau, khỏe khoắn, vừa đi làm vừa chở hàng.',
                'attrs' => [
                    ['nhien_lieu', 'Nhiên liệu', 'select', null, $fuel, true, false],
                    ['hop_so', 'Hộp số', 'select', null, $gear, true, true],
                    ['dan_dong', 'Hệ dẫn động', 'select', null, ['Một cầu 4x2', 'Hai cầu 4x4'], true, true],
                    ['dong_co', 'Động cơ', 'text', null, null, true, false],
                    ['tai_trong', 'Tải trọng thùng', 'number', 'kg', null, false, false],
                ],
            ],
            'Hatchback' => [
                'desc' => 'Nhỏ gọn, dễ xoay xở trong phố, chi phí thấp.',
                'attrs' => [
                    ['nhien_lieu', 'Nhiên liệu', 'select', null, $fuel, true, false],
                    ['hop_so', 'Hộp số', 'select', null, $gear, true, true],
                    ['dong_co', 'Động cơ', 'text', null, null, true, false],
                    ['tui_khi', 'Số túi khí', 'number', null, null, false, true],
                ],
            ],
            'Xe điện' => [
                'desc' => 'Không phát thải, vận hành êm, chi phí sạc thấp.',
                'attrs' => [
                    ['so_cho', 'Số chỗ ngồi', 'select', null, ['4 chỗ', '5 chỗ', '7 chỗ'], true, true],
                    ['dung_luong_pin', 'Dung lượng pin', 'number', 'kWh', null, true, false],
                    ['quang_duong', 'Quãng đường mỗi lần sạc', 'number', 'km', null, true, true],
                    ['sac_nhanh', 'Sạc nhanh', 'text', null, null, false, false],
                    ['cong_suat', 'Công suất', 'number', 'mã lực', null, false, false],
                ],
            ],
        ];

        $categories = [];
        $i = 0;
        foreach ($definitions as $name => $def) {
            $cat = Category::create([
                'name' => $name, 'slug' => Str::slug($name), 'description' => $def['desc'], 'sort_order' => $i++,
            ]);
            foreach ($def['attrs'] as $k => [$code, $label, $type, $unit, $options, $req, $filter]) {
                Attribute::create([
                    'category_id' => $cat->id, 'code' => $code, 'name' => $label, 'type' => $type,
                    'unit' => $unit, 'options' => $options, 'is_required' => $req,
                    'is_filterable' => $filter, 'sort_order' => $k,
                ]);
            }
            $categories[$name] = $cat;
        }

        /* ---------------- Xe ----------------
         * [dòng xe, hãng, tên, năm, tình trạng, km, màu, mã màu, giá, giá KM, tồn kho, trạng thái, nổi bật, thông số theo thứ tự khai báo]
         */
        $cars = [
            ['Sedan', 'Toyota', 'Toyota Vios 1.5G CVT', 2025, 'new', 0, 'Trắng ngọc trai', '#eef1f4', 545_000_000, 520_000_000, 8, 'published', true, ['Xăng', 'Số tự động vô cấp CVT', '1.5L 4 xi-lanh', 106, 5.7]],
            ['Sedan', 'Honda', 'Honda City RS', 2025, 'new', 0, 'Đỏ cá tính', '#c8102e', 569_000_000, null, 6, 'published', false, ['Xăng', 'Số tự động vô cấp CVT', '1.5L i-VTEC', 119, 5.6]],
            ['Sedan', 'Toyota', 'Toyota Camry 2.5HEV Top', 2025, 'new', 0, 'Đen ánh kim', '#1b1d22', 1_530_000_000, null, 3, 'published', true, ['Hybrid (Xăng – Điện)', 'Số tự động vô cấp CVT', '2.5L Hybrid', 227, 4.4]],
            ['Sedan', 'Mazda', 'Mazda 3 1.5 Luxury', 2025, 'new', 0, 'Xám xi măng', '#8a8f94', 699_000_000, 669_000_000, 5, 'published', false, ['Xăng', 'Số tự động', '1.5L SkyActiv-G', 110, 6.2]],
            ['Sedan', 'Kia', 'Kia K3 2.0 Premium', 2025, 'new', 0, 'Xanh dương', '#1d4e89', 659_000_000, null, 4, 'published', false, ['Xăng', 'Số tự động', '2.0L MPI', 150, 6.8]],
            ['Sedan', 'Mercedes-Benz', 'Mercedes-Benz C 300 AMG', 2024, 'new', 0, 'Bạc Iridium', '#c0c4c8', 2_099_000_000, 1_999_000_000, 2, 'published', true, ['Xăng', 'Số tự động', '2.0L Turbo + EQ Boost', 258, 7.1]],
            ['Sedan', 'Hyundai', 'Hyundai Accent 1.5 AT', 2022, 'used', 38_000, 'Trắng', '#f3f3f3', 449_000_000, 429_000_000, 1, 'published', false, ['Xăng', 'Số tự động', '1.5L Smartstream', 113, 5.9]],

            ['SUV / Crossover', 'Toyota', 'Toyota Corolla Cross 1.8HEV', 2025, 'new', 0, 'Xanh rêu', '#4b5d3f', 905_000_000, null, 5, 'published', true, ['5 chỗ', 'Hybrid (Xăng – Điện)', 'Số tự động vô cấp CVT', 'Cầu trước (FWD)', '1.8L Hybrid', 120]],
            ['SUV / Crossover', 'Honda', 'Honda CR-V e:HEV RS', 2025, 'new', 0, 'Xanh đêm', '#1c2b4a', 1_259_000_000, null, 3, 'published', true, ['7 chỗ', 'Hybrid (Xăng – Điện)', 'Số tự động', 'Cầu trước (FWD)', '2.0L e:HEV', 204]],
            ['SUV / Crossover', 'Mazda', 'Mazda CX-5 2.0 Premium', 2025, 'new', 0, 'Đỏ pha lê', '#9e1b32', 829_000_000, 799_000_000, 7, 'published', true, ['5 chỗ', 'Xăng', 'Số tự động', 'Cầu trước (FWD)', '2.0L SkyActiv-G', 154]],
            ['SUV / Crossover', 'Hyundai', 'Hyundai Santa Fe 2.5 Turbo Calligraphy', 2025, 'new', 0, 'Nâu đồng', '#6b4f3a', 1_365_000_000, null, 2, 'published', false, ['7 chỗ', 'Xăng', 'Số tự động', 'Hai cầu toàn thời gian (AWD)', '2.5L Turbo', 281]],
            ['SUV / Crossover', 'Kia', 'Kia Seltos 1.5 Premium', 2025, 'new', 0, 'Cam hoàng hôn', '#d9652b', 699_000_000, null, 6, 'published', false, ['5 chỗ', 'Xăng', 'Số tự động vô cấp CVT', 'Cầu trước (FWD)', '1.5L Smartstream', 115]],
            ['SUV / Crossover', 'Ford', 'Ford Everest Titanium+ 4x4', 2025, 'new', 0, 'Xám Meteor', '#5a5f66', 1_545_000_000, null, 2, 'published', true, ['7 chỗ', 'Dầu (Diesel)', 'Số tự động', 'Hai cầu 4x4', '2.0L Bi-Turbo', 210]],
            ['SUV / Crossover', 'Mitsubishi', 'Mitsubishi Xpander AT Premium', 2025, 'new', 0, 'Bạc', '#b8bcc0', 658_000_000, 638_000_000, 9, 'published', false, ['7 chỗ', 'Xăng', 'Số tự động vô cấp CVT', 'Cầu trước (FWD)', '1.5L MIVEC', 105]],
            ['SUV / Crossover', 'Toyota', 'Toyota Fortuner 2.4 AT 4x2', 2021, 'used', 62_000, 'Đen', '#202124', 899_000_000, null, 1, 'published', false, ['7 chỗ', 'Dầu (Diesel)', 'Số tự động', 'Cầu trước (FWD)', '2.4L Diesel', 148]],

            ['Bán tải', 'Ford', 'Ford Ranger Wildtrak 2.0 4x4', 2025, 'new', 0, 'Cam Luxe', '#d46a1f', 979_000_000, null, 4, 'published', true, ['Dầu (Diesel)', 'Số tự động', 'Hai cầu 4x4', '2.0L Bi-Turbo', 650]],
            ['Bán tải', 'Toyota', 'Toyota Hilux 2.8 Adventure 4x4', 2025, 'new', 0, 'Trắng', '#f1f1f1', 1_005_000_000, null, 3, 'published', false, ['Dầu (Diesel)', 'Số tự động', 'Hai cầu 4x4', '2.8L Diesel', 600]],
            ['Bán tải', 'Mitsubishi', 'Mitsubishi Triton Athlete 4x4', 2025, 'new', 0, 'Xám đậm', '#3d4146', 924_000_000, 899_000_000, 5, 'published', false, ['Dầu (Diesel)', 'Số tự động', 'Hai cầu 4x4', '2.4L Bi-Turbo', 630]],

            ['Hatchback', 'Toyota', 'Toyota Wigo 1.2 AT', 2025, 'new', 0, 'Vàng chanh', '#e4c22b', 405_000_000, null, 10, 'published', false, ['Xăng', 'Số tự động vô cấp CVT', '1.2L', 2]],
            ['Hatchback', 'Hyundai', 'Hyundai Grand i10 1.2 AT', 2025, 'new', 0, 'Xanh ngọc', '#2a9d8f', 435_000_000, 415_000_000, 8, 'published', false, ['Xăng', 'Số tự động', '1.2L Kappa', 2]],
            ['Hatchback', 'Kia', 'Kia Morning GT-Line', 2025, 'new', 0, 'Đỏ', '#b3202a', 424_000_000, null, 6, 'published', false, ['Xăng', 'Số tự động', '1.25L Kappa', 6]],
            ['Hatchback', 'Mazda', 'Mazda 2 Sport 1.5 Luxury', 2023, 'used', 21_500, 'Xanh khói', '#5c7c8a', 455_000_000, null, 1, 'published', false, ['Xăng', 'Số tự động', '1.5L SkyActiv-G', 6]],

            ['Xe điện', 'VinFast', 'VinFast VF 3', 2025, 'new', 0, 'Xanh lá Summer', '#7cc16b', 299_000_000, null, 12, 'published', true, ['4 chỗ', 18.64, 215, 'DC 30 phút (10–70%)', 43]],
            ['Xe điện', 'VinFast', 'VinFast VF 5 Plus', 2025, 'new', 0, 'Vàng Cam', '#f0a830', 529_000_000, 499_000_000, 9, 'published', false, ['5 chỗ', 37.23, 326, 'DC 30 phút (10–70%)', 134]],
            ['Xe điện', 'VinFast', 'VinFast VF 6 Plus', 2025, 'new', 0, 'Xanh Dương Đậm', '#16407a', 765_000_000, null, 6, 'published', true, ['5 chỗ', 59.6, 460, 'DC 25 phút (10–70%)', 201]],
            ['Xe điện', 'VinFast', 'VinFast VF 8 Plus', 2025, 'new', 0, 'Đỏ Crimson', '#9b1c31', 1_199_000_000, 1_149_000_000, 3, 'published', false, ['5 chỗ', 87.7, 471, 'DC 31 phút (10–70%)', 402]],
            ['Xe điện', 'Hyundai', 'Hyundai Ioniq 5 Prestige', 2024, 'new', 0, 'Bạc Gravity', '#a7abaf', 1_300_000_000, null, 2, 'published', false, ['5 chỗ', 72.6, 451, '800V – 18 phút (10–80%)', 214]],

            // Dữ liệu chưa hoàn thiện để minh họa PIM
            ['SUV / Crossover', 'Kia', 'Kia Sorento 2.2D Signature', 2025, 'new', 0, null, '#2e3a46', 1_219_000_000, null, 2, 'review', false, ['7 chỗ', 'Dầu (Diesel)', 'Số tự động', null, '2.2L Smartstream D', 198]],
            ['Xe điện', 'VinFast', 'VinFast VF 9 Plus', 2025, 'new', 0, 'Trắng Brahminy', '#f2f2f2', 1_699_000_000, null, 0, 'draft', false, ['7 chỗ', null, null, null, 402]],
        ];

        $sequence = 1001;
        foreach ($cars as [$catName, $brandName, $name, $year, $cond, $km, $color, $hex, $price, $sale, $stock, $status, $featured, $values]) {
            $category = $categories[$catName];
            $media = $this->carMedia($name, $catName);
            $car = Car::create([
                'category_id' => $category->id,
                'brand_id' => $brands[$brandName]->id,
                'sku' => 'XE'.$sequence++,
                'name' => $name,
                'slug' => Str::slug($name.'-'.$year),
                'year' => $year,
                'condition' => $cond,
                'mileage' => $km,
                'color' => $color,
                'color_hex' => $hex,
                'price' => $price,
                'sale_price' => $sale,
                'stock' => $stock,
                'status' => $status,
                'is_featured' => $featured,
                'image' => $media['image'],
                'gallery' => $media['gallery'],
                'short_description' => $status === 'draft' ? null : $this->shortText($catName, $name, $cond),
                'description' => $status === 'draft' ? null : $this->longText($catName, $name, $year, $cond, $km),
                'views' => random_int(20, 600),
            ]);

            // Duyệt thông số theo đúng thứ tự khai báo (orderBy sort_order, id) để không bị gán lệch.
            foreach ($category->attributes()->get()->values() as $idx => $attribute) {
                $value = $values[$idx] ?? null;
                if ($value !== null && $value !== '') {
                    $car->attributeValues()->create(['attribute_id' => $attribute->id, 'value' => (string) $value]);
                }
            }

            $car->refreshCompleteness();
        }

        /* ---------------- Đơn hàng & lái thử mẫu ---------------- */
        $this->sampleOrders($customer);

        TestDrive::create([
            'car_id' => Car::where('sku', 'XE1009')->value('id'), 'user_id' => $customer->id,
            'name' => $customer->name, 'phone' => $customer->phone, 'email' => $customer->email,
            'showroom' => config('autoshop.showrooms')[0], 'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time' => '09:30', 'note' => 'Muốn chạy thử cung đường cao tốc.', 'status' => 'new',
        ]);
        TestDrive::create([
            'car_id' => Car::where('sku', 'XE1023')->value('id'), 'name' => 'Trần Thị Bình', 'phone' => '0912345678',
            'showroom' => config('autoshop.showrooms')[1], 'preferred_date' => now()->addDays(4)->toDateString(),
            'preferred_time' => '14:00', 'status' => 'confirmed',
        ]);
    }

    private function sampleOrders(User $customer): void
    {
        $samples = [
            ['XE1024', 'vnpay', 'paid', 'completed', 150],
            ['XE1019', 'showroom', 'paid', 'completed', 120],
            ['XE1013', 'vnpay', 'paid', 'completed', 95],
            ['XE1003', 'showroom', 'paid', 'completed', 62],
            ['XE1010', 'vnpay', 'deposited', 'delivering', 30],
            ['XE1001', 'vnpay', 'deposited', 'confirmed', 5],
            ['XE1016', 'showroom', 'unpaid', 'pending', 1],
        ];

        foreach ($samples as $n => [$sku, $method, $payStatus, $status, $daysAgo]) {
            $car = Car::where('sku', $sku)->first();
            $subtotal = $car->effectivePrice();
            $reg = (int) round($subtotal * config('autoshop.registration_rate_new') / 100) + config('autoshop.plate_fee');
            $total = $subtotal + $reg;
            $deposit = max(config('autoshop.min_deposit'), (int) (round($total * config('autoshop.deposit_percent') / 100 / 1000) * 1000));
            $date = now()->subDays($daysAgo);

            $order = Order::create([
                'code' => 'AS'.$date->format('ymd').strtoupper(Str::random(5)),
                'user_id' => $customer->id,
                'customer_name' => $customer->name, 'customer_phone' => $customer->phone,
                'customer_email' => $customer->email, 'customer_id_number' => '079200001234',
                'address' => '25 Nguyễn Huệ, Q.1, TP.HCM', 'showroom' => config('autoshop.showrooms')[$n % 4],
                'subtotal' => $subtotal, 'registration_fee' => $reg, 'total' => $total, 'deposit_amount' => $deposit,
                'payment_method' => $method, 'payment_status' => $payStatus, 'status' => $status,
            ]);
            $order->forceFill(['created_at' => $date, 'updated_at' => $date])->saveQuietly();

            $order->items()->create([
                'car_id' => $car->id, 'name' => $car->name, 'sku' => $car->sku, 'color' => $car->color,
                'year' => $car->year, 'price' => $subtotal, 'quantity' => 1,
            ]);
            $car->decrement('stock');

            PaymentTransaction::create([
                'order_id' => $order->id, 'gateway' => $method,
                'transaction_code' => $order->code.'01',
                'amount' => $payStatus === 'paid' ? $total : $deposit,
                'status' => in_array($payStatus, ['deposited', 'paid']) ? 'success' : 'pending',
                'gateway_transaction_no' => $method === 'vnpay' && $payStatus !== 'unpaid' ? (string) random_int(14000000, 14999999) : null,
                'paid_at' => in_array($payStatus, ['deposited', 'paid']) ? $date : null,
            ]);
        }
    }

    private function shortText(string $cat, string $name, string $cond): string
    {
        $tag = $cond === 'used' ? 'Xe đã qua sử dụng, kiểm định 176 hạng mục' : 'Xe mới 100%, bảo hành chính hãng';

        return match ($cat) {
            'Sedan' => "$name – sedan thanh lịch, vận hành êm ái. $tag.",
            'SUV / Crossover' => "$name – gầm cao rộng rãi cho cả gia đình. $tag.",
            'Bán tải' => "$name – mạnh mẽ, đa dụng, sẵn sàng mọi cung đường. $tag.",
            'Hatchback' => "$name – nhỏ gọn, linh hoạt trong đô thị. $tag.",
            default => "$name – xe điện thông minh, không phát thải. $tag.",
        };
    }

    private function longText(string $cat, string $name, int $year, string $cond, int $km): string
    {
        $intro = $cond === 'used'
            ? "$name đời $year, đã đi ".number_format($km, 0, ',', '.')." km, một chủ từ đầu, lịch sử bảo dưỡng đầy đủ tại hãng. Xe đã qua kiểm định chất lượng của AutoShop và được bảo hành 12 tháng hoặc 20.000 km."
            : "$name phiên bản $year được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.";

        return $intro."\n\nTrang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.\n\nKhi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.";
    }

    private function carMedia(string $name, string $cat): array
    {
        $media = [
            'Toyota Vios 1.5G CVT' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Toyota_Vios_1.5_VVT-i_G_%28IV%29_%E2%80%93_f_13032025.jpg/1280px-Toyota_Vios_1.5_VVT-i_G_%28IV%29_%E2%80%93_f_13032025.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Toyota_Vios_1.5_VVT-i_G_%28IV%29_%E2%80%93_f_13032025.jpg/1280px-Toyota_Vios_1.5_VVT-i_G_%28IV%29_%E2%80%93_f_13032025.jpg',
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/Toyota_Vios_1.5_VVT-i_G_%28IV%29_%E2%80%93_r_13032025.jpg/1280px-Toyota_Vios_1.5_VVT-i_G_%28IV%29_%E2%80%93_r_13032025.jpg',
                ],
            ],
            'Honda City RS' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Honda_City_1.5_i-VTEC_V_%28VIII%2C_Facelift%29_%E2%80%93_f_22032025.jpg/1280px-Honda_City_1.5_i-VTEC_V_%28VIII%2C_Facelift%29_%E2%80%93_f_22032025.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Honda_City_1.5_i-VTEC_V_%28VIII%2C_Facelift%29_%E2%80%93_f_22032025.jpg/1280px-Honda_City_1.5_i-VTEC_V_%28VIII%2C_Facelift%29_%E2%80%93_f_22032025.jpg',
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/2/23/Honda_City_1.5_i-VTEC_V_%28VIII%2C_Facelift%29_%E2%80%93_r_22032025.jpg/1280px-Honda_City_1.5_i-VTEC_V_%28VIII%2C_Facelift%29_%E2%80%93_r_22032025.jpg',
                ],
            ],
            'Toyota Camry 2.5HEV Top' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/2018_Toyota_Camry_%28ASV70R%29_Ascent_sedan_%282018-08-27%29_01.jpg/1280px-2018_Toyota_Camry_%28ASV70R%29_Ascent_sedan_%282018-08-27%29_01.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/2018_Toyota_Camry_%28ASV70R%29_Ascent_sedan_%282018-08-27%29_01.jpg/1280px-2018_Toyota_Camry_%28ASV70R%29_Ascent_sedan_%282018-08-27%29_01.jpg',
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/2021_Toyota_Camry_GR_Sport_white_front.jpg/1280px-2021_Toyota_Camry_GR_Sport_white_front.jpg',
                ],
            ],
            'Mazda 3 1.5 Luxury' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Mazda3_SEDAN_20S_L_Package_2WD_%285BA-BPFP%29_front.jpg/1280px-Mazda3_SEDAN_20S_L_Package_2WD_%285BA-BPFP%29_front.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Mazda3_SEDAN_20S_L_Package_2WD_%285BA-BPFP%29_front.jpg/1280px-Mazda3_SEDAN_20S_L_Package_2WD_%285BA-BPFP%29_front.jpg',
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/6/69/Mazda3_SEDAN_XD_L_Package_%283DA-BP8P%29_front.jpg/1280px-Mazda3_SEDAN_XD_L_Package_%283DA-BP8P%29_front.jpg',
                ],
            ],
            'Kia K3 2.0 Premium' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/60/2021_Kia_Cerato_GT-Line_Limited_Edition_front_left.jpg/1280px-2021_Kia_Cerato_GT-Line_Limited_Edition_front_left.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/6/60/2021_Kia_Cerato_GT-Line_Limited_Edition_front_left.jpg/1280px-2021_Kia_Cerato_GT-Line_Limited_Edition_front_left.jpg',
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/2021_Kia_Cerato_GT-Line_Limited_Edition_front_right.jpg/1280px-2021_Kia_Cerato_GT-Line_Limited_Edition_front_right.jpg',
                ],
            ],
            'Mercedes-Benz C 300 AMG' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Mercedes-Benz_W206_IMG_5796.jpg/1280px-Mercedes-Benz_W206_IMG_5796.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Mercedes-Benz_W206_IMG_5796.jpg/1280px-Mercedes-Benz_W206_IMG_5796.jpg',
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/5/50/Mercedes-AMG_C_43_%28W206%29_IMG_7393.jpg/1280px-Mercedes-AMG_C_43_%28W206%29_IMG_7393.jpg',
                ],
            ],
            'Hyundai Accent 1.5 AT' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/2019_Hyundai_Accent_1.6L%2C_front_10.8.19.jpg/1280px-2019_Hyundai_Accent_1.6L%2C_front_10.8.19.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/2019_Hyundai_Accent_1.6L%2C_front_10.8.19.jpg/1280px-2019_Hyundai_Accent_1.6L%2C_front_10.8.19.jpg',
                ],
            ],
            'Toyota Corolla Cross 1.8HEV' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/2023_Toyota_Corolla_Cross_XLE_4WD_in_Wind_Chill_Pearl%2C_front_left.jpg/1280px-2023_Toyota_Corolla_Cross_XLE_4WD_in_Wind_Chill_Pearl%2C_front_left.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/2023_Toyota_Corolla_Cross_XLE_4WD_in_Wind_Chill_Pearl%2C_front_left.jpg/1280px-2023_Toyota_Corolla_Cross_XLE_4WD_in_Wind_Chill_Pearl%2C_front_left.jpg',
                ],
            ],
            'Honda CR-V e:HEV RS' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Honda_CR-V_e-HEV_Elegance_AWD_%28VI%29_%E2%80%93_f_14072024.jpg/1280px-Honda_CR-V_e-HEV_Elegance_AWD_%28VI%29_%E2%80%93_f_14072024.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Honda_CR-V_e-HEV_Elegance_AWD_%28VI%29_%E2%80%93_f_14072024.jpg/1280px-Honda_CR-V_e-HEV_Elegance_AWD_%28VI%29_%E2%80%93_f_14072024.jpg',
                ],
            ],
            'Mazda CX-5 2.0 Premium' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/2024_Mazda_CX-5_2.5_S_Select_in_Platinum_Quartz_Metallic%2C_front_right.jpg/1280px-2024_Mazda_CX-5_2.5_S_Select_in_Platinum_Quartz_Metallic%2C_front_right.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/2024_Mazda_CX-5_2.5_S_Select_in_Platinum_Quartz_Metallic%2C_front_right.jpg/1280px-2024_Mazda_CX-5_2.5_S_Select_in_Platinum_Quartz_Metallic%2C_front_right.jpg',
                ],
            ],
            'Hyundai Santa Fe 2.5 Turbo Calligraphy' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/2024_Hyundai_Santa_Fe_Luxury_AWD_in_Hampton_Grey%2C_front_left%2C_2024-06-30.jpg/1280px-2024_Hyundai_Santa_Fe_Luxury_AWD_in_Hampton_Grey%2C_front_left%2C_2024-06-30.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/2024_Hyundai_Santa_Fe_Luxury_AWD_in_Hampton_Grey%2C_front_left%2C_2024-06-30.jpg/1280px-2024_Hyundai_Santa_Fe_Luxury_AWD_in_Hampton_Grey%2C_front_left%2C_2024-06-30.jpg',
                ],
            ],
            'Kia Seltos 1.5 Premium' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/Kia_Seltos_SP2_PE_Snow_White_Pearl_%2817%29_%28cropped%29.jpg/1280px-Kia_Seltos_SP2_PE_Snow_White_Pearl_%2817%29_%28cropped%29.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/Kia_Seltos_SP2_PE_Snow_White_Pearl_%2817%29_%28cropped%29.jpg/1280px-Kia_Seltos_SP2_PE_Snow_White_Pearl_%2817%29_%28cropped%29.jpg',
                ],
            ],
            'Ford Everest Titanium+ 4x4' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ab/Ford_Everest_3.0_V6_Turbo_Diesel_4WD_Platinum_%28III%29_%E2%80%93_f_02012026.jpg/1280px-Ford_Everest_3.0_V6_Turbo_Diesel_4WD_Platinum_%28III%29_%E2%80%93_f_02012026.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ab/Ford_Everest_3.0_V6_Turbo_Diesel_4WD_Platinum_%28III%29_%E2%80%93_f_02012026.jpg/1280px-Ford_Everest_3.0_V6_Turbo_Diesel_4WD_Platinum_%28III%29_%E2%80%93_f_02012026.jpg',
                ],
            ],
            'Mitsubishi Xpander AT Premium' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/2018_Mitsubishi_Xpander_Ultimate_1.5_NC1W_%2820190623%29.jpg/1280px-2018_Mitsubishi_Xpander_Ultimate_1.5_NC1W_%2820190623%29.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/2018_Mitsubishi_Xpander_Ultimate_1.5_NC1W_%2820190623%29.jpg/1280px-2018_Mitsubishi_Xpander_Ultimate_1.5_NC1W_%2820190623%29.jpg',
                ],
            ],
            'Toyota Fortuner 2.4 AT 4x2' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/2015_Toyota_Fortuner_%28New_Zealand%29.jpg/1280px-2015_Toyota_Fortuner_%28New_Zealand%29.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/2015_Toyota_Fortuner_%28New_Zealand%29.jpg/1280px-2015_Toyota_Fortuner_%28New_Zealand%29.jpg',
                ],
            ],
            'Ford Ranger Wildtrak 2.0 4x4' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/Ford_Ranger_2.0_EcoBlue_4WD_Wildtrak_X_%28IV%29_%E2%80%93_f_12102024.jpg/1280px-Ford_Ranger_2.0_EcoBlue_4WD_Wildtrak_X_%28IV%29_%E2%80%93_f_12102024.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/Ford_Ranger_2.0_EcoBlue_4WD_Wildtrak_X_%28IV%29_%E2%80%93_f_12102024.jpg/1280px-Ford_Ranger_2.0_EcoBlue_4WD_Wildtrak_X_%28IV%29_%E2%80%93_f_12102024.jpg',
                ],
            ],
            'Toyota Hilux 2.8 Adventure 4x4' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/2016_Toyota_HiLux_Invincible_D-4D_4WD_2.4_Front.jpg/1280px-2016_Toyota_HiLux_Invincible_D-4D_4WD_2.4_Front.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/2016_Toyota_HiLux_Invincible_D-4D_4WD_2.4_Front.jpg/1280px-2016_Toyota_HiLux_Invincible_D-4D_4WD_2.4_Front.jpg',
                ],
            ],
            'Mitsubishi Triton Athlete 4x4' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/27/Mitsubishi_Triton_LC_2.4_GLS_2WD_Blade_Silver_Metallic_%28cropped%29.jpg/1280px-Mitsubishi_Triton_LC_2.4_GLS_2WD_Blade_Silver_Metallic_%28cropped%29.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/2/27/Mitsubishi_Triton_LC_2.4_GLS_2WD_Blade_Silver_Metallic_%28cropped%29.jpg/1280px-Mitsubishi_Triton_LC_2.4_GLS_2WD_Blade_Silver_Metallic_%28cropped%29.jpg',
                ],
            ],
            'Toyota Wigo 1.2 AT' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7f/2024_Toyota_Agya_1.2_G_%28front%29%2C_West_Surabaya.jpg/1280px-2024_Toyota_Agya_1.2_G_%28front%29%2C_West_Surabaya.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7f/2024_Toyota_Agya_1.2_G_%28front%29%2C_West_Surabaya.jpg/1280px-2024_Toyota_Agya_1.2_G_%28front%29%2C_West_Surabaya.jpg',
                ],
            ],
            'Hyundai Grand i10 1.2 AT' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Hyundai_i10_1.0_Intro_%28III%29_%E2%80%93_f_03012021.jpg/1280px-Hyundai_i10_1.0_Intro_%28III%29_%E2%80%93_f_03012021.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Hyundai_i10_1.0_Intro_%28III%29_%E2%80%93_f_03012021.jpg/1280px-Hyundai_i10_1.0_Intro_%28III%29_%E2%80%93_f_03012021.jpg',
                ],
            ],
            'Kia Morning GT-Line' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/2018_Kia_Picanto_3_Automatic_1.2_Front.jpg/1280px-2018_Kia_Picanto_3_Automatic_1.2_Front.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/2018_Kia_Picanto_3_Automatic_1.2_Front.jpg/1280px-2018_Kia_Picanto_3_Automatic_1.2_Front.jpg',
                ],
            ],
            'Mazda 2 Sport 1.5 Luxury' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/2021_Mazda2_GT_Sport_NAV_MHEV_1.5_Front.jpg/1280px-2021_Mazda2_GT_Sport_NAV_MHEV_1.5_Front.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/2021_Mazda2_GT_Sport_NAV_MHEV_1.5_Front.jpg/1280px-2021_Mazda2_GT_Sport_NAV_MHEV_1.5_Front.jpg',
                ],
            ],
            'VinFast VF 3' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/33/2025_VinFast_VF_3%2C_front_left.jpg/1280px-2025_VinFast_VF_3%2C_front_left.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/3/33/2025_VinFast_VF_3%2C_front_left.jpg/1280px-2025_VinFast_VF_3%2C_front_left.jpg',
                ],
            ],
            'VinFast VF 5 Plus' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/a/a9/VinFast_VF5.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/a/a9/VinFast_VF5.jpg',
                ],
            ],
            'VinFast VF 6 Plus' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/VinFast_VF_6_DSC_8468.jpg/1280px-VinFast_VF_6_DSC_8468.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/VinFast_VF_6_DSC_8468.jpg/1280px-VinFast_VF_6_DSC_8468.jpg',
                ],
            ],
            'VinFast VF 8 Plus' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/61/VinFast_VF_8_DSC_8568.jpg/1280px-VinFast_VF_8_DSC_8568.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/6/61/VinFast_VF_8_DSC_8568.jpg/1280px-VinFast_VF_8_DSC_8568.jpg',
                ],
            ],
            'Hyundai Ioniq 5 Prestige' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/85/Hyundai_Ioniq_5_AWD_Techniq-Paket_%E2%80%93_f_31122024.jpg/1280px-Hyundai_Ioniq_5_AWD_Techniq-Paket_%E2%80%93_f_31122024.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/8/85/Hyundai_Ioniq_5_AWD_Techniq-Paket_%E2%80%93_f_31122024.jpg/1280px-Hyundai_Ioniq_5_AWD_Techniq-Paket_%E2%80%93_f_31122024.jpg',
                ],
            ],
            'Kia Sorento 2.2D Signature' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/84/2024_Kia_Sorento_X-Line_SX_Prestige_%28facelift%29%2C_front_12.20.24.jpg/1280px-2024_Kia_Sorento_X-Line_SX_Prestige_%28facelift%29%2C_front_12.20.24.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/8/84/2024_Kia_Sorento_X-Line_SX_Prestige_%28facelift%29%2C_front_12.20.24.jpg/1280px-2024_Kia_Sorento_X-Line_SX_Prestige_%28facelift%29%2C_front_12.20.24.jpg',
                ],
            ],
            'VinFast VF 9 Plus' => [
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7a/VinFast_VF9%2C_front_NYIAS_2022.jpg/1280px-VinFast_VF9%2C_front_NYIAS_2022.jpg',
                'gallery' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7a/VinFast_VF9%2C_front_NYIAS_2022.jpg/1280px-VinFast_VF9%2C_front_NYIAS_2022.jpg',
                ],
            ],
        ];

        if (isset($media[$name])) {
            return $media[$name];
        }

        return [
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Toyota_Vios_1.5_VVT-i_G_%28IV%29_%E2%80%93_f_13032025.jpg/1280px-Toyota_Vios_1.5_VVT-i_G_%28IV%29_%E2%80%93_f_13032025.jpg',
            'gallery' => [],
        ];
    }
}
