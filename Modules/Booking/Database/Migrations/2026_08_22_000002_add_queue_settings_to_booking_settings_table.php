<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_settings', 'queue_enabled')) {
                    $table->boolean('queue_enabled')->default(false)->after('global_online_booking_enabled');
                }
                if (!Schema::hasColumn('booking_settings', 'queue_max_size')) {
                    $table->unsignedInteger('queue_max_size')->nullable()->after('queue_enabled');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (Schema::hasColumn('booking_settings', 'queue_max_size')) {
                    $table->dropColumn('queue_max_size');
                }
                if (Schema::hasColumn('booking_settings', 'queue_enabled')) {
                    $table->dropColumn('queue_enabled');
                }
            });
        }
    }
};
