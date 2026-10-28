<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Danh mục (dòng xe), thương hiệu, định nghĩa thông số kỹ thuật (EAV),
 * xe và giá trị thông số của từng xe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country', 60)->nullable();
            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60);
            $table->string('name');
            $table->string('type', 20)->default('text'); // text | number | select
            $table->string('unit', 20)->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['category_id', 'code']);
        });

        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 40)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('year');
            $table->string('condition', 10)->default('new'); // new | used
            $table->unsignedInteger('mileage')->default(0); // km đã đi
            $table->string('color', 40)->nullable();
            $table->string('color_hex', 7)->default('#1f6feb');
            $table->unsignedBigInteger('price');          // VND, số nguyên
            $table->unsignedBigInteger('sale_price')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('status', 20)->default('draft'); // draft | review | published
            $table->unsignedTinyInteger('completeness')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->text('image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('short_description', 255)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'category_id']);
        });

        Schema::create('car_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();
            $table->unique(['car_id', 'attribute_id']);
            $table->index(['attribute_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_attribute_values');
        Schema::dropIfExists('cars');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
