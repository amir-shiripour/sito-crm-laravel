<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('calendar_events')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                if (!Schema::hasColumn('calendar_events', 'recurrence_type')) {
                    $table->string('recurrence_type', 20)->nullable()->after('is_public')->index();
                }
                if (!Schema::hasColumn('calendar_events', 'recurrence_interval')) {
                    $table->unsignedSmallInteger('recurrence_interval')->default(1)->after('recurrence_type');
                }
                if (!Schema::hasColumn('calendar_events', 'recurrence_days')) {
                    $table->json('recurrence_days')->nullable()->after('recurrence_interval');
                }
                if (!Schema::hasColumn('calendar_events', 'repeat_until')) {
                    $table->date('repeat_until')->nullable()->after('recurrence_days')->index();
                }
                if (!Schema::hasColumn('calendar_events', 'repeat_count')) {
                    $table->unsignedSmallInteger('repeat_count')->nullable()->after('repeat_until');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('calendar_events')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $columns = ['recurrence_type', 'recurrence_interval', 'recurrence_days', 'repeat_until', 'repeat_count'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('calendar_events', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
