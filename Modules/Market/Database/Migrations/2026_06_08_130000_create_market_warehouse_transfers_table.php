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
        Schema::create('market_warehouse_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_warehouse_id')->constrained('market_warehouses')->onDelete('cascade');
            $table->foreignId('destination_warehouse_id')->constrained('market_warehouses')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('market_product_variants')->onDelete('cascade');
            $table->foreignId('vendor_product_id')->nullable()->constrained('market_vendor_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
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
        Schema::dropIfExists('market_warehouse_transfers');
    }
};
