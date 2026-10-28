# AutoShop – Website bán ô tô (Laravel 13 · PHP 8.3 · MySQL 8 · Bootstrap 5)

Đồ án môn **Lập trình mã nguồn mở**: website thương mại điện tử bán ô tô, gồm
quản lý dữ liệu xe theo thông số động (PIM/EAV), đặt cọc trực tuyến qua VNPay
(có cổng giả lập), đăng ký lái thử, so sánh xe, tính trả góp và trang quản trị.

> Thiết kế chi tiết của đề tài (mục tiêu, phân tích, CSDL, luồng nghiệp vụ): xem
> [`docs/THIET_KE_DE_TAI.md`](docs/THIET_KE_DE_TAI.md).

---

## 0. Yêu cầu môi trường

- PHP ≥ 8.3 (bật extension: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`; chạy test cần thêm `pdo_sqlite`)
- Composer 2.x, MySQL 8.x (hoặc MariaDB 10.6+)
- Không cần Node.js: Bootstrap 5.3 đã được đặt sẵn trong `public/vendor/bootstrap/`, CSS/JS giao diện nằm ở `public/css/app.css`, `public/js/app.js`.

## 1. Chạy trên Laragon (Windows)

Thư mục dự án đặt tại **`C:\laragon\www\AutoShop`** hoặc **`E:\laragon\www\AutoShop`** (hoặc thư mục bất kỳ). File `setup.bat` tự động phát hiện Laragon ở ổ **`E:\`**, **`C:\`** hoặc **`D:\`**.

1. Mở **Laragon** → bấm **Start All** (Apache + MySQL phải chạy).
2. Nhấp đúp **`setup.bat`** trong thư mục `AutoShop`. File này tự động:
   - cài thư viện bằng Composer (nếu chưa có `vendor/`),
   - tạo `.env` (nếu chưa có) và khóa ứng dụng,
   - tạo database `autoshop` (utf8mb4) trong MySQL của Laragon,
   - chạy `php artisan migrate:fresh --seed` (11 bảng nghiệp vụ + dữ liệu mẫu 29 xe kèm ảnh thật & gallery),
   - tạo `storage:link` để hiển thị ảnh xe tải lên.
3. Trong Laragon: **Menu → Apache → Reload** (để Laragon nhận virtual host mới).
4. Mở trình duyệt: **http://autoshop.test**
   (hoặc http://localhost/AutoShop/public).

### Chạy thủ công bằng Terminal của Laragon

```bat
composer install
copy .env.example .env
php artisan key:generate
mysql -u root -e "CREATE DATABASE IF NOT EXISTS autoshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve        # mở http://127.0.0.1:8000
```

> Trên macOS/Linux dùng `cp .env.example .env` thay cho `copy`. Nếu MySQL có mật khẩu, sửa `DB_PASSWORD` trong `.env` trước khi migrate.

### Nhập database có sẵn (không cần migrate)

File **`database/autoshop.sql`** chứa toàn bộ cấu trúc + dữ liệu mẫu.
Laragon → **Database** (HeidiSQL) → *File → Run SQL file…* → chọn `autoshop.sql`.

## 2. Tài khoản demo

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị viên | admin@autoshop.test | password |
| Khách hàng | khach@autoshop.test | password |

## 3. Cấu hình `.env` chính

| Biến | Ý nghĩa | Giá trị mặc định |
|---|---|---|
| `APP_URL` | URL gốc (dùng làm Return URL VNPay) | `http://autoshop.test` |
| `APP_TIMEZONE` | Múi giờ (ảnh hưởng hạn thanh toán 15 phút) | `Asia/Ho_Chi_Minh` |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | MySQL của Laragon | `autoshop` / `root` / (trống) |
| `VNP_TMN_CODE`, `VNP_HASH_SECRET` | Thông tin VNPay sandbox. **Để trống = dùng cổng giả lập** | (trống) |
| `DEPOSIT_PERCENT`, `MIN_DEPOSIT` | % tiền cọc và mức cọc tối thiểu | `10`, `10000000` |

## 4. Chức năng

**Khách hàng**
- Trang chủ: banner trình chiếu xe nổi bật, ô tìm xe nhanh (hãng/giá/tình trạng), dòng xe, xe giảm giá, hãng xe.
- Danh sách xe: tìm kiếm, lọc theo hãng, khoảng giá, xe mới/cũ, đời xe, **lọc theo thông số động của từng dòng xe** (số chỗ, nhiên liệu, dẫn động, quãng đường pin…), sắp xếp, phân trang giữ bộ lọc.
- Chi tiết xe: **thư viện ảnh xe thực tế (Photo Gallery tương tác nhiều góc chụp: trước, sau, nội thất)**, bảng thông số, mô tả, **công cụ tính trả góp**, **đăng ký lái thử**, xe tương tự.
- **So sánh tối đa 3 xe**, **xe yêu thích**.
- Giỏ xe → đặt mua: tính **chi phí lăn bánh ước tính** (trước bạ 10% xe mới / 2% xe cũ + biển số, đăng kiểm) và **tiền đặt cọc 10%**.
- Đặt cọc **VNPay** (ký HMAC‑SHA512, IPN, idempotent) hoặc **đặt cọc tại showroom**.
- Tài khoản: **cập nhật thông tin cá nhân (họ tên, email, SĐT)**, **đổi mật khẩu an toàn**, lịch sử đơn hàng, tự hủy đơn chưa cọc, lịch lái thử, xe yêu thích. Chế độ **sáng/tối**.

**Quản trị (`/admin`)**
- Dashboard: doanh số 6 tháng, tiền đã thu, đơn theo trạng thái, tỉ lệ thanh toán theo kênh, xe bán chạy, xe sắp hết, dữ liệu chưa hoàn thiện, lịch lái thử mới.
- **Quản lý người dùng (`/admin/users`)**: danh sách tài khoản khách hàng và quản trị, phân quyền role, tìm kiếm/lọc, thêm mới, chỉnh sửa thông tin, đổi mật khẩu và xóa tài khoản.
- Quản lý xe (PIM): thông số thay đổi theo dòng xe, **% hoàn thiện dữ liệu**, quy trình **Nháp → Chờ duyệt → Đang bán**, **chặn xuất bản khi thiếu thông số bắt buộc**, tải ảnh thật hoặc nhập link URL và quản lý gallery nhiều ảnh.
- Dòng xe, hãng xe, thông số kỹ thuật: **đủ Thêm – Xem – Sửa – Xóa** (form sửa mở trong Bootstrap Modal); thêm thông số mới không cần sửa code.
- Đơn đặt xe với **máy trạng thái** (không cho nhảy cóc; hủy đơn hoàn xe về kho).
- Lịch lái thử, **nhập/xuất CSV** (mẫu: `database/sample-import.csv`).

## 5. Lệnh hữu ích

| Lệnh | Tác dụng |
|---|---|
| `php artisan migrate:fresh --seed` | Tạo lại toàn bộ bảng + dữ liệu mẫu |
| `php artisan orders:cancel-expired` | Hủy đơn VNPay quá 15 phút chưa cọc, hoàn xe về kho |
| `php artisan schedule:work` (hoặc `run-scheduler.bat`) | Chạy lịch tự hủy đơn mỗi 5 phút |
| `php artisan test` | Chạy 25 kịch bản kiểm thử tự động (SQLite trong bộ nhớ) |
| `php artisan route:list` | Liệt kê route |
| `php artisan optimize:clear` | Xóa cache cấu hình/view/route |

## 6. Kỹ thuật Laravel áp dụng (theo yêu cầu đồ án)

| Yêu cầu | Cách thực hiện trong mã nguồn |
|---|---|
| Eloquent ORM + quan hệ | `Category hasMany Car`, `Brand hasMany Car`, `Category hasMany Attribute`, `Car hasMany CarAttributeValue`, `User hasMany Order`, `Order hasMany OrderItem`, `User belongsToMany Car` (bảng `wishlists`) |
| Migration / Seeder / Factory | `database/migrations`, `DatabaseSeeder` (dữ liệu thật), `UserFactory`, `CategoryFactory`, `BrandFactory`, `CarFactory` |
| Phân quyền | Laravel Auth (session) + middleware `auth` và `admin` (`App\Http\Middleware\AdminOnly`) bảo vệ toàn bộ `/admin` |
| Validation | Form Request trong `app/Http/Requests` (`CarRequest`, `CategoryRequest`, `BrandRequest`, `AttributeRequest`, `UserRequest`, `CheckoutRequest`, `RegisterRequest`), thông báo lỗi tiếng Việt |
| Tìm kiếm + phân trang giữ từ khóa | `paginate()->withQueryString()` ở danh sách xe (cửa hàng và quản trị), đơn hàng, người dùng |
| Flash message | `->with('success'/'error')` hiển thị bằng Bootstrap Toast (cửa hàng) và Bootstrap Alert (quản trị) |
| Giao diện | Blade + Bootstrap 5.3 (grid, modal, toast, alert, table) kết hợp CSS riêng; tách layout `layouts/app` (khách) và `layouts/admin` (quản trị) |
| Mở rộng | Upload ảnh qua Laravel Storage, AJAX tải thông số theo dòng xe, Soft Delete cho xe, middleware tùy chỉnh, responsive, chế độ Sáng/Tối, cổng VNPay |

## 7. Cấu trúc thư mục chính

```
AutoShop/
├── app/
│   ├── Http/Controllers/          Shop, Cart, Checkout, Payment, TestDrive, Auth, Account
│   │   └── Admin/                 Dashboard, Car, Catalog, Order, TestDrive, User, ImportExport
│   ├── Http/Middleware/AdminOnly.php
│   ├── Http/Requests/             Form Request (validation) cho khách và quản trị
│   ├── Models/                    User, Category, Brand, Attribute, Car, CarAttributeValue,
│   │                              Order, OrderItem, PaymentTransaction, TestDrive
│   ├── Services/                  CartService, OrderService, PaymentService
│   └── Support/helpers.php        vnd(), vnd_short(), body_type()
├── config/autoshop.php            Cọc, phí lăn bánh, showroom, VNPay
├── database/
│   ├── migrations/                Lược đồ CSDL
│   ├── factories/                 User, Category, Brand, Car factory
│   ├── seeders/DatabaseSeeder.php 9 hãng, 5 dòng xe, 29 xe, đơn & lịch lái thử mẫu
│   ├── autoshop.sql               Bản xuất MySQL (cấu trúc + dữ liệu)
│   └── sample-import.csv          Tệp CSV mẫu để thử nhập
├── docs/THIET_KE_DE_TAI.md        Tài liệu thiết kế đề tài
├── lang/vi/                       Thông báo tiếng Việt
├── public/css/app.css, public/js/app.js   Giao diện (không cần build Node)
├── public/vendor/bootstrap/       Bootstrap 5.3 (CSS + JS bundle)
├── resources/views/               Blade: shop, cart, checkout, payment, account, auth, admin
├── routes/web.php, routes/console.php
├── tests/Feature/                ShopFlowTest (luồng mua/thanh toán), CatalogCrudTest (CRUD + quyền)
├── setup.bat, run-scheduler.bat
└── .env, .env.example
```
