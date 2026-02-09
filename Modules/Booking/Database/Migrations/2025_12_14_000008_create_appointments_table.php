<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('appointments')) {
            return;
        }

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('provider_user_id');
            $table->unsignedBigInteger('client_id');

            $table->string('status', 30)->default('DRAFT');

            $table->timestamp('start_at_utc');
            $table->timestamp('end_at_utc');

            $table->string('created_by_type', 30);
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->text('notes')->nullable();
            $table->json('appointment_form_response_json')->nullable();

            $table->unsignedBigInteger('rescheduled_from_appointment_id')->nullable();
            $table->string('cancel_reason')->nullable();

            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('booking_services')->cascadeOnDelete();
            $table->foreign('provider_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('rescheduled_from_appointment_id')->references('id')->on('appointments')->nullOnDelete();

            $table->index(['provider_user_id', 'start_at_utc']);
            $table->index(['service_id', 'start_at_utc']);
            $table->index(['status', 'start_at_utc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
