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
        Schema::create('warehouse_item_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->string('batch_code', 100)->nullable()->index(); // کد پارت یا شماره فاکتور
            $table->date('purchase_date')->nullable();
            $table->decimal('unit_purchase_price', 15, 2)->default(0); // قیمت خرید هر واحد در این پارت
            $table->decimal('initial_quantity', 12, 2)->default(0); // تعداد اولیه پارت
            $table->decimal('remaining_quantity', 12, 2)->default(0); // تعداد باقی‌مانده در انبار از این پارت
            $table->date('expiry_date')->nullable()->index(); // تاریخ انقضای این پارت
            $table->string('supplier', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on('warehouse_items')
                ->cascadeOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouse_warehouses')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_item_batches');
    }
};
