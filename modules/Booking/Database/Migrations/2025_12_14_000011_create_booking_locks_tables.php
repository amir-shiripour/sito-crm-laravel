<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_day_locks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('provider_user_id');
            $table->date('local_date');

            $table->timestamps();

            $table->unique(['service_id', 'provider_user_id', 'local_date'], 'booking_day_lock_unique');
        });

        Schema::create('booking_slot_locks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('provider_user_id');

            $table->timestamp('start_at_utc');
            $table->timestamp('end_at_utc');

            $table->timestamps();

            $table->unique(['service_id', 'provider_user_id', 'start_at_utc', 'end_at_utc'], 'booking_slot_lock_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_slot_locks');
        Schema::dropIfExists('booking_day_locks');
    }
};
