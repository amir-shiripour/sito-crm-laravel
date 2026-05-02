<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول اصلی فاکتور
        Schema::create('market_orders', function (Blueprint $table) {
            $table->id();

            // اتصال خریدار به جدول clients
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // مبالغ کل
            $table->decimal('total_items_price', 15, 2);
            $table->decimal('total_shipping_cost', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);

            // اطلاعات لجستیک و پرداخت
            $table->json('shipping_address_json')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('delivery_status')->default('processing');

            $table->text('customer_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // جدول اقلام فاکتور
        Schema::create('market_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('market_orders')->cascadeOnDelete();

            // 💡 تغییر کلیدی: حالا اقلام سفارش به جدول محصولات فروشنده وصل میشن
            $table->foreignId('vendor_product_id')->constrained('market_vendor_products')->cascadeOnDelete();

            $table->foreignId('vendor_id')->constrained('market_vendors')->cascadeOnDelete();

            $table->string('product_title');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('unit_tax', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2);
            $table->decimal('vendor_commission_rate', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_order_items');
        Schema::dropIfExists('market_orders');
    }
};
