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
        Schema::create('market_shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('conditions')->nullable(); // brand_ids, category_ids, product_ids, variant_ids
            $table->decimal('min_grand_total', 15, 2)->nullable();
            $table->string('action_type')->default('free_shipping'); // free_shipping, percentage_discount, fixed_discount
            $table->decimal('action_value', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('market_shipping_rules');
    }
};
