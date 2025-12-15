<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_service_providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('provider_user_id');

            $table->boolean('is_active')->default(true);
            $table->boolean('customization_enabled')->default(false);

            $table->string('override_price_mode', 20)->default('INHERIT');
            $table->decimal('override_base_price', 14, 2)->nullable();
            $table->decimal('override_discount_price', 14, 2)->nullable();
            $table->timestamp('override_discount_from')->nullable();
            $table->timestamp('override_discount_to')->nullable();

            $table->string('override_online_booking_mode', 20)->nullable();

            $table->string('override_status_mode', 20)->default('INHERIT');
            $table->string('override_status', 20)->nullable();

            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('booking_services')->cascadeOnDelete();
            $table->foreign('provider_user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['service_id', 'provider_user_id']);
            $table->index(['provider_user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_service_providers');
    }
};
