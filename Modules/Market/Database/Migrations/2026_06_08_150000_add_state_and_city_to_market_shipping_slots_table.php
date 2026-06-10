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
        Schema::table('market_shipping_slots', function (Blueprint $table) {
            $table->string('state')->nullable()->after('shipping_method_id');
            $table->string('city')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('market_shipping_slots', function (Blueprint $table) {
            $table->dropColumn(['state', 'city']);
        });
    }
};
