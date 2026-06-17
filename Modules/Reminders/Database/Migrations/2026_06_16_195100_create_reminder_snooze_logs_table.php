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
        Schema::create('reminder_snooze_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reminder_id');
            $table->unsignedBigInteger('user_id'); // چه کسی تعویق زد
            $table->timestamp('original_remind_at'); // زمان یادآوری قبل از این تعویق
            $table->timestamp('snoozed_to'); // زمان جدید پس از تعویق
            $table->string('duration_key', 20); // '15m', '1h', '1d', 'custom'
            $table->unsignedInteger('duration_minutes'); // مدت تعویق به دقیقه
            $table->string('reason')->nullable(); // دلیل تعویق
            $table->unsignedInteger('snooze_sequence'); // شماره ترتیبی این تعویق (1, 2, 3...)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('reminder_id')
                ->references('id')
                ->on('reminders')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->index('reminder_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_snooze_logs');
    }
};
