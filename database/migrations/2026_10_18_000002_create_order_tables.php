<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đơn đặt mua xe, dòng đơn (snapshot), giao dịch thanh toán và lịch lái thử.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->string('customer_id_number', 20)->nullable(); // CCCD để làm hồ sơ đăng ký xe
            $table->string('address');
            $table->string('showroom', 100);       // đại lý nhận xe
            $table->text('note')->nullable();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('registration_fee'); // phí trước bạ + biển số ước tính
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('deposit_amount');   // tiền đặt cọc cần trả
            $table->string('payment_method', 20);           // showroom | vnpay
            $table->string('payment_status', 20)->default('unpaid'); // unpaid | deposited | paid | failed
            $table->string('status', 20)->default('pending'); // pending | confirmed | delivering | completed | cancelled
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'payment_status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku', 40);
            $table->string('color', 40)->nullable();
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 20);                 // vnpay | showroom
            $table->string('transaction_code', 40)->unique();
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('pending'); // pending | success | failed
            $table->string('gateway_transaction_no')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('test_drives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('showroom', 100);
            $table->date('preferred_date');
            $table->string('preferred_time', 5); // HH:MM
            $table->text('note')->nullable();
            $table->string('status', 20)->default('new'); // new | confirmed | done | cancelled
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'car_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('test_drives');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
