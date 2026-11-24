<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->string('system_code', 60)->unique()->index();
            $table->string('code', 100)->unique()->index();
            $table->string('name')->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();
            $table->string('brand', 100)->nullable()->index();
            $table->string('brand_code', 50)->nullable()->index();
            $table->string('item_type', 100)->nullable()->index(); // جنس / نوع / تیپ
            $table->string('item_type_code', 50)->nullable()->index();
            $table->string('model_spec', 100)->nullable(); // سایز / رنگ / مشخصه
            $table->string('unit', 50)->default('عدد');
            $table->decimal('min_stock', 12, 2)->default(0); // حداقل موجودی اضطراری
            $table->decimal('reorder_point', 12, 2)->default(0); // نقطه سفارش مجدد
            $table->decimal('current_price', 15, 2)->nullable(); // نرخ روز بازار
            $table->decimal('last_purchase_price', 15, 2)->nullable(); // آخرین فی خرید
            $table->unsignedBigInteger('default_warehouse_id')->nullable()->index();
            $table->string('default_location', 255)->nullable();
            $table->string('barcode', 100)->nullable()->index();
            $table->boolean('has_expiry')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')
                ->references('id')
                ->on('warehouse_categories')
                ->nullOnDelete();

            $table->foreign('subcategory_id')
                ->references('id')
                ->on('warehouse_categories')
                ->nullOnDelete();

            $table->foreign('default_warehouse_id')
                ->references('id')
                ->on('warehouse_warehouses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_items');
    }
};
