<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('market_shipping_slot_bookings')->truncate();
        \DB::table('market_shipping_slots')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        Schema::table('market_shipping_slots', function (Blueprint $table) {
            $table->dropColumn(['day_of_week', 'state', 'city']);
            $table->json('days')->nullable()->after('shipping_method_id');
            $table->json('states')->nullable()->after('days');
            $table->json('cities')->nullable()->after('states');
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
            $table->dropColumn(['days', 'states', 'cities']);
            $table->integer('day_of_week')->default(0);
            $table->string('state')->nullable();
            $table->string('city')->nullable();
        });
    }
};
