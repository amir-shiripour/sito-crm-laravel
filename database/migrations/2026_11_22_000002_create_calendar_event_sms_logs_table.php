<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('calendar_event_sms_logs')) {
            Schema::create('calendar_event_sms_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('calendar_event_id')
                    ->constrained('calendar_events')
                    ->cascadeOnDelete();
                $table->date('occurrence_date')->index();
                $table->unsignedSmallInteger('rule_index')->default(0);
                $table->string('recipient_phone', 20);
                $table->unsignedBigInteger('sms_message_id')->nullable()->index();
                $table->string('status', 20)->default('dispatched');
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['calendar_event_id', 'occurrence_date', 'rule_index', 'recipient_phone'],
                    'cal_ev_sms_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_sms_logs');
    }
};
