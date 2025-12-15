<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_availability_exceptions', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 30); // GLOBAL / SERVICE / SERVICE_PROVIDER
            $table->unsignedBigInteger('scope_id')->nullable();

            $table->date('local_date');
            $table->boolean('is_closed')->default(false);

            $table->json('override_work_windows_json')->nullable();
            $table->json('override_breaks_json')->nullable();

            $table->unsignedInteger('override_capacity_per_slot')->nullable();
            $table->unsignedInteger('override_capacity_per_day')->nullable();

            $table->timestamps();

            $table->index(['scope_type', 'scope_id', 'local_date'], 'bk_av_ex_scope_date_idx');
            $table->unique(['scope_type', 'scope_id', 'local_date'], 'booking_availability_ex_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_availability_exceptions');
    }
};
