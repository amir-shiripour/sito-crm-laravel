<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_settings', 'laboratory_enabled')) {
                    $table->boolean('laboratory_enabled')->default(false)->after('monitoring_refresh_interval_seconds');
                }
                if (!Schema::hasColumn('booking_settings', 'laboratory_default_partner')) {
                    $table->string('laboratory_default_partner', 100)->nullable()->default('آرمان سلامت')->after('laboratory_enabled');
                }
                if (!Schema::hasColumn('booking_settings', 'laboratory_settings')) {
                    $table->json('laboratory_settings')->nullable()->after('laboratory_default_partner');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                $columns = ['laboratory_enabled', 'laboratory_default_partner', 'laboratory_settings'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('booking_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
