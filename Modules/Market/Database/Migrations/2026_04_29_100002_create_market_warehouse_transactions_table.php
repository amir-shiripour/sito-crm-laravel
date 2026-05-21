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
        Schema::create('market_warehouse_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('market_warehouses')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('market_product_variants')->onDelete('cascade');
            $table->foreignId('vendor_product_id')->nullable()->constrained('market_vendor_products')->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'reserve_add', 'reserve_release', 'adjustment']);
            $table->integer('quantity');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('market_warehouse_transactions');
    }
};
