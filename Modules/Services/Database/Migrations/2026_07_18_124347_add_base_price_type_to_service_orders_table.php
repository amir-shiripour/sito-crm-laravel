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
        Schema::table('service_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('service_orders', 'base_price_type')) {
                $table->string('base_price_type')->default('auto')->after('first_payment_amount'); // وضعیت قیمت نهایی سرویس در فاکتور: 'auto' یا 'manual'
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
        Schema::table('service_orders', function (Blueprint $table) {
            if (Schema::hasColumn('service_orders', 'base_price_type')) {
                $table->dropColumn('base_price_type');
            }
        });
    }
};
