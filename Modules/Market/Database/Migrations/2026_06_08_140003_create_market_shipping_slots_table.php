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
        Schema::create('market_shipping_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained('market_shipping_methods')->onDelete('cascade');
            $table->integer('day_of_week'); // 0 (Sunday) to 6 (Saturday)
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('capacity')->default(0); // maximum bookings allowed
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
        Schema::dropIfExists('market_shipping_slots');
    }
};
