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
        Schema::create('market_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained('market_shipping_methods')->onDelete('cascade');
            $table->foreignId('shipping_zone_id')->constrained('market_shipping_zones')->onDelete('cascade');
            $table->integer('min_weight')->default(0); // weight in grams
            $table->integer('max_weight')->default(9999999); // weight in grams
            $table->decimal('min_order_price', 15, 2)->default(0.00); // min price threshold
            $table->decimal('cost', 15, 2)->default(0.00); // base price
            $table->decimal('per_kg_cost', 15, 2)->default(0.00); // extra cost per kg beyond base weight
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('market_shipping_rates');
    }
};
