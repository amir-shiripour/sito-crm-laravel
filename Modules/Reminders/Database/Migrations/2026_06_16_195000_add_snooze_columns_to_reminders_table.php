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
        Schema::table('reminders', function (Blueprint $table) {
            $table->unsignedInteger('snooze_count')->default(0)->after('status');
            $table->timestamp('original_remind_at')->nullable()->after('remind_at');
            $table->timestamp('last_snoozed_at')->nullable()->after('snooze_count');
            $table->unsignedBigInteger('last_snoozed_by')->nullable()->after('last_snoozed_at');

            $table->foreign('last_snoozed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropForeign(['last_snoozed_by']);
            $table->dropColumn([
                'snooze_count',
                'original_remind_at',
                'last_snoozed_at',
                'last_snoozed_by',
            ]);
        });
    }
};
