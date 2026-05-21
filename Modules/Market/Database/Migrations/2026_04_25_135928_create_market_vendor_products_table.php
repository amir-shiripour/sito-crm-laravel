<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('market_vendor_products', function (Blueprint $table) {
            $table->id();
            // فرض بر این است که مایگریشن market_vendors قبلا اجرا شده است
            $table->foreignId('vendor_id')->constrained('market_vendors')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('market_product_variants')->cascadeOnDelete();

            $table->string('sku_extension')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('discount_price', 15, 2)->nullable();

            // 💡 NEW: فیلدهای مدیریت تخفیف
            $table->timestamp('discount_start_date')->nullable();
            $table->timestamp('discount_end_date')->nullable();
            $table->integer('discount_stock')->nullable(); // تعداد موجودی در تخفیف
            $table->integer('max_discount_purchase_qty')->nullable(); // حداکثر خرید در تخفیف

            $table->integer('stock')->default(0);
            $table->integer('reorder_point')->default(5);
            $table->integer('min_purchase_qty')->default(1);
            $table->integer('max_purchase_qty')->nullable();

            $table->enum('status', ['draft', 'pending_review', 'published', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('market_vendor_products');
    }
};
