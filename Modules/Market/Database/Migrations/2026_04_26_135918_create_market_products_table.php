<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // ۱. جدول برندها
        Schema::create('market_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('code_prefix')->unique();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ۲. کاتالوگ اصلی محصولات (Master)
        Schema::create('market_master_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('market_brands');
            $table->foreignId('category_id')->constrained('market_categories');

            $table->string('crm_code')->unique(); // Smart SKU اصلی
            $table->string('barcode')->nullable()->unique();
            $table->string('title');
            $table->string('slug')->unique();

            // 💡 مدیا و تصاویر
            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable();

            $table->longText('description')->nullable();
            $table->json('attributes')->nullable(); // ویژگی‌های عمومی کالا

            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        // 💡 ۳. تنوع‌های محصول (Variants)
        Schema::create('market_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_product_id')->constrained('market_master_products')->cascadeOnDelete();

            $table->string('variant_code')->unique(); // ساب اس‌کیو (مثلا SIT-3000100001-V1)
            $table->json('variant_attributes'); // ویژگی‌های متغیر (مثل: {"رنگ": "مشکی", "حافظه": "256GB"})
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // ۴. تنوع فروشندگان (Vendor Products)
        Schema::create('market_vendor_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('market_vendors')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('market_product_variants')->cascadeOnDelete();

            $table->string('sku_extension')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('reorder_point')->default(5);
            $table->integer('min_purchase_qty')->default(1); // 💡 فیلد حداقل سفارش
            $table->integer('max_purchase_qty')->nullable(); // 💡 فیلد حداکثر سفارش

            $table->enum('status', ['draft', 'pending_review', 'published', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable(); // 💡 دلیل رد محصول توسط مدیریت

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('market_vendor_products');
        Schema::dropIfExists('market_product_variants');
        Schema::dropIfExists('market_master_products');
        Schema::dropIfExists('market_brands');
    }
};
