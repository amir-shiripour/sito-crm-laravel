<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('booking_laboratory_daily_logs')) {
            Schema::create('booking_laboratory_daily_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('booking_laboratory_orders')->cascadeOnDelete();
                $table->foreignId('stage_id')->nullable()->constrained('booking_laboratory_stages')->nullOnDelete();
                $table->date('log_date')->index();
                $table->boolean('needs_followup')->default(true);
                $table->text('followup_result')->nullable();
                $table->foreignId('operator_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_laboratory_daily_logs');
    }
};
