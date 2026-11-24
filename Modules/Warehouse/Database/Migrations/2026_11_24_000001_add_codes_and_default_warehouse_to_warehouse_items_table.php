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
        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->string('brand_code', 50)->nullable()->after('brand');
            $table->string('item_type_code', 50)->nullable()->after('item_type');
            $table->unsignedBigInteger('default_warehouse_id')->nullable()->after('last_purchase_price');

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
        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->dropForeign(['default_warehouse_id']);
            $table->dropColumn(['brand_code', 'item_type_code', 'default_warehouse_id']);
        });
    }
};
