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
        Schema::create('market_shipping_slot_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_slot_id')->constrained('market_shipping_slots')->onDelete('cascade');
            $table->date('booking_date');
            $table->integer('orders_count')->default(0);
            $table->timestamps();

            $table->unique(['shipping_slot_id', 'booking_date'], 'slot_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('market_shipping_slot_bookings');
    }
};
