<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_slot_holds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('provider_user_id');
            $table->string('client_temp_key')->nullable();

            $table->timestamp('start_at_utc');
            $table->timestamp('end_at_utc');

            $table->timestamp('expires_at_utc');
            $table->timestamp('created_at');

            $table->foreign('service_id')->references('id')->on('booking_services')->cascadeOnDelete();
            $table->foreign('provider_user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['provider_user_id', 'start_at_utc']);
            $table->index(['expires_at_utc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_slot_holds');
    }
};
