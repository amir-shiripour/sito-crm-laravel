<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id')->nullable();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status', 20)->default('ACTIVE');

            $table->decimal('base_price', 14, 2)->default(0);
            $table->decimal('discount_price', 14, 2)->nullable();
            $table->timestamp('discount_from')->nullable();
            $table->timestamp('discount_to')->nullable();

            $table->unsignedBigInteger('category_id')->nullable();

            $table->string('online_booking_mode', 20)->default('INHERIT');

            $table->string('payment_mode', 20)->default('NONE');
            $table->string('payment_amount_type', 20)->nullable();
            $table->decimal('payment_amount_value', 14, 2)->nullable();

            $table->unsignedBigInteger('appointment_form_id')->nullable();
            $table->json('client_profile_required_fields')->nullable();

            $table->boolean('provider_can_customize')->default(false);

            $table->timestamps();

            $table->foreign('owner_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('booking_categories')->nullOnDelete();
            $table->foreign('appointment_form_id')->references('id')->on('booking_forms')->nullOnDelete();

            $table->index(['status', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};
