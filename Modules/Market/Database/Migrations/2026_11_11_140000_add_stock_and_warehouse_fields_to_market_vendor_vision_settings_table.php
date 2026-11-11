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
        Schema::table('market_vendor_vision_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('market_vendor_vision_settings', 'stock_field')) {
                $table->string('stock_field')->default('AllT')->after('compare_price_field');
            }
            if (!Schema::hasColumn('market_vendor_vision_settings', 'crm_warehouse_id')) {
                $table->unsignedBigInteger('crm_warehouse_id')->nullable()->after('default_warehouse_code');
                $table->foreign('crm_warehouse_id')
                    ->references('id')
                    ->on('market_warehouses')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('market_vendor_vision_settings', function (Blueprint $table) {
            if (Schema::hasColumn('market_vendor_vision_settings', 'crm_warehouse_id')) {
                $table->dropForeign(['crm_warehouse_id']);
                $table->dropColumn('crm_warehouse_id');
            }
            if (Schema::hasColumn('market_vendor_vision_settings', 'stock_field')) {
                $table->dropColumn('stock_field');
            }
        });
    }
};
