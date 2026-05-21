<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('market_warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('market_warehouses')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('market_product_variants')->onDelete('cascade');
            $table->foreignId('vendor_product_id')->nullable()->constrained('market_vendor_products')->onDelete('cascade');
            $table->integer('physical_stock')->default(0);
            $table->integer('reserved_stock')->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_variant_id', 'vendor_product_id'], 'market_warehouse_stocks_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('market_warehouse_stocks');
    }
};
