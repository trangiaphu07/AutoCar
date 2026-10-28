<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TestDriveController;
use Illuminate\Support\Facades\Route;

/* ---------------- Cửa hàng ---------------- */
Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/xe', [ShopController::class, 'index'])->name('cars.index');
Route::get('/dong-xe/{category:slug}', [ShopController::class, 'category'])->name('cars.category');
Route::get('/xe/{slug}', [ShopController::class, 'show'])->name('cars.show');
Route::get('/so-sanh', [ShopController::class, 'compare'])->name('cars.compare');
Route::post('/so-sanh/{car}', [ShopController::class, 'toggleCompare'])->name('cars.compare.toggle');

/* ---------------- Giỏ xe ---------------- */
Route::get('/gio-xe', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-xe/{car}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/gio-xe/{car}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-xe/{car}', [CartController::class, 'remove'])->name('cart.remove');

/* ---------------- Đặt mua & thanh toán ---------------- */
Route::get('/dat-mua', [CheckoutController::class, 'form'])->name('checkout.form');
Route::post('/dat-mua', [CheckoutController::class, 'place'])->middleware('throttle:10,1')->name('checkout.place');
Route::get('/don-hang/{order:code}', [CheckoutController::class, 'result'])->name('orders.result');
Route::post('/don-hang/{order:code}/thanh-toan-lai', [CheckoutController::class, 'retry'])->name('orders.retry');

Route::get('/payment/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
Route::match(['get', 'post'], '/payment/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payment.vnpay.ipn');
Route::get('/payment/mock/{code}', [PaymentController::class, 'mock'])->name('payment.mock');
Route::post('/payment/mock/{code}', [PaymentController::class, 'mockSubmit'])->name('payment.mock.submit');

/* ---------------- Lái thử ---------------- */
Route::post('/lai-thu/{car}', [TestDriveController::class, 'store'])->middleware('throttle:10,1')->name('testdrive.store');

/* ---------------- Tài khoản ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/dang-ky', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:6,1');
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('tai-khoan')->name('account.')->group(function () {
    Route::get('/thong-tin', [AccountController::class, 'profile'])->name('profile');
    Route::put('/thong-tin', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/doi-mat-khau', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::get('/don-hang', [AccountController::class, 'orders'])->name('orders');
    Route::post('/don-hang/{order:code}/huy', [AccountController::class, 'cancel'])->name('orders.cancel');
    Route::get('/lai-thu', [AccountController::class, 'testDrives'])->name('testdrives');
    Route::get('/yeu-thich', [AccountController::class, 'wishlist'])->name('wishlist');
    Route::post('/yeu-thich/{car}', [AccountController::class, 'toggleWishlist'])->name('wishlist.toggle');
});

/* ---------------- Quản trị: chỉ tài khoản role=admin (middleware auth + admin) ---------------- */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', Admin\UserController::class)->except('show');

    Route::get('/cars/attributes/{category}', [Admin\CarController::class, 'attributeFields'])->name('cars.attributes');
    Route::resource('cars', Admin\CarController::class)->except('show');

    Route::get('/catalog', [Admin\CatalogController::class, 'index'])->name('catalog');
    Route::post('/catalog/categories', [Admin\CatalogController::class, 'storeCategory'])->name('catalog.categories.store');
    Route::put('/catalog/categories/{category}', [Admin\CatalogController::class, 'updateCategory'])->name('catalog.categories.update');
    Route::delete('/catalog/categories/{category}', [Admin\CatalogController::class, 'destroyCategory'])->name('catalog.categories.destroy');
    Route::post('/catalog/brands', [Admin\CatalogController::class, 'storeBrand'])->name('catalog.brands.store');
    Route::put('/catalog/brands/{brand}', [Admin\CatalogController::class, 'updateBrand'])->name('catalog.brands.update');
    Route::delete('/catalog/brands/{brand}', [Admin\CatalogController::class, 'destroyBrand'])->name('catalog.brands.destroy');
    Route::post('/catalog/attributes', [Admin\CatalogController::class, 'storeAttribute'])->name('catalog.attributes.store');
    Route::put('/catalog/attributes/{attribute}', [Admin\CatalogController::class, 'updateAttribute'])->name('catalog.attributes.update');
    Route::delete('/catalog/attributes/{attribute}', [Admin\CatalogController::class, 'destroyAttribute'])->name('catalog.attributes.destroy');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/confirm-transfer', [Admin\OrderController::class, 'confirmTransfer'])->name('orders.confirm-transfer');

    Route::get('/test-drives', [Admin\TestDriveController::class, 'index'])->name('testdrives.index');
    Route::patch('/test-drives/{testDrive}', [Admin\TestDriveController::class, 'update'])->name('testdrives.update');

    Route::get('/import-export', [Admin\ImportExportController::class, 'index'])->name('io.index');
    Route::get('/export', [Admin\ImportExportController::class, 'export'])->name('io.export');
    Route::post('/import', [Admin\ImportExportController::class, 'import'])->name('io.import');
});
