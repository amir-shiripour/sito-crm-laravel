<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_availability_rules', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 30); // GLOBAL / SERVICE / SERVICE_PROVIDER
            $table->unsignedBigInteger('scope_id')->nullable();

            $table->unsignedTinyInteger('weekday'); // 0..6
            $table->boolean('is_closed')->default(false);

            $table->time('work_start_local')->nullable();
            $table->time('work_end_local')->nullable();
            $table->json('breaks_json')->nullable();

            $table->unsignedInteger('slot_duration_minutes')->nullable();
            $table->unsignedInteger('capacity_per_slot')->nullable();
            $table->unsignedInteger('capacity_per_day')->nullable();

            $table->timestamps();

            $table->index(['scope_type', 'scope_id', 'weekday']);
            $table->unique(['scope_type', 'scope_id', 'weekday'], 'booking_availability_rules_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_availability_rules');
    }
};
