<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('booking_client_schedules')) {
            Schema::create('booking_client_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->json('weekly_schedule')->nullable();
                $table->json('preferred_days')->nullable();
                $table->string('preferred_shift', 30)->nullable()->default('any');
                $table->unsignedTinyInteger('max_appointments_per_week')->nullable();
                $table->boolean('temp_absence_active')->default(false);
                $table->date('temp_absence_start')->nullable();
                $table->date('temp_absence_end')->nullable();
                $table->string('temp_absence_reason', 255)->nullable();
                $table->text('general_notes')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique('client_id');
                $table->index(['temp_absence_active', 'temp_absence_start', 'temp_absence_end'], 'idx_bcs_absence');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_client_schedules');
    }
};
