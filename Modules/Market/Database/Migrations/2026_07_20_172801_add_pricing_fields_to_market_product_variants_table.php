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
        Schema::table('market_product_variants', function (Blueprint $table) {
            $table->decimal('discount_price', 15, 2)->nullable()->after('price');
            $table->timestamp('discount_start_date')->nullable()->after('discount_price');
            $table->timestamp('discount_end_date')->nullable()->after('discount_start_date');
            $table->integer('discount_stock')->nullable()->after('discount_end_date');
            $table->integer('max_discount_purchase_qty')->nullable()->after('discount_stock');
            $table->integer('reorder_point')->default(5)->after('max_discount_purchase_qty');
            $table->integer('min_purchase_qty')->default(1)->after('reorder_point');
            $table->integer('max_purchase_qty')->nullable()->after('min_purchase_qty');
            $table->decimal('cart_amount_step', 15, 2)->nullable()->after('max_purchase_qty');
            $table->integer('purchase_step')->nullable()->after('cart_amount_step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_product_variants', function (Blueprint $table) {
            $table->dropColumn([
                'discount_price',
                'discount_start_date',
                'discount_end_date',
                'discount_stock',
                'max_discount_purchase_qty',
                'reorder_point',
                'min_purchase_qty',
                'max_purchase_qty',
                'cart_amount_step',
                'purchase_step'
            ]);
        });
    }
};
