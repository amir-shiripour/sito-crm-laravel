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
        Schema::table('services', function (Blueprint $table) {
            $table->json('renewal_prices')->nullable()->after('base_price');
            $table->dropColumn(['renewal_price', 'renewal_price_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('renewal_prices');
            $table->integer('renewal_price')->default(0);
            $table->string('renewal_price_type')->default('price');
        });
    }
};
