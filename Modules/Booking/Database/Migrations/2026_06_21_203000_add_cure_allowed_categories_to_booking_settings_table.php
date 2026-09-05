<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_settings', 'cure_allowed_categories')) {
                    $col = $table->json('cure_allowed_categories')->nullable();
                    if (Schema::hasColumn('booking_settings', 'cure_show_tooth_filter')) {
                        $col->after('cure_show_tooth_filter');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (Schema::hasColumn('booking_settings', 'cure_allowed_categories')) {
                    $table->dropColumn('cure_allowed_categories');
                }
            });
        }
    }
};
