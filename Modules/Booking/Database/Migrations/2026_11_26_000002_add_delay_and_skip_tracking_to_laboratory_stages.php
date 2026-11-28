<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_laboratory_stages')) {
            Schema::table('booking_laboratory_stages', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_laboratory_stages', 'is_skipped')) {
                    $table->boolean('is_skipped')->default(false)->after('is_completed')->index();
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'was_completed_late')) {
                    $table->boolean('was_completed_late')->default(false)->after('is_skipped')->index();
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'delay_minutes')) {
                    $table->unsignedInteger('delay_minutes')->nullable()->after('was_completed_late');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_laboratory_stages')) {
            Schema::table('booking_laboratory_stages', function (Blueprint $table) {
                $columns = ['is_skipped', 'was_completed_late', 'delay_minutes'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('booking_laboratory_stages', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
