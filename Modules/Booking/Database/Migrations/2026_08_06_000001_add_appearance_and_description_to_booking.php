<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_settings', 'show_service_description')) {
                    $table->boolean('show_service_description')->default(true);
                }
                if (!Schema::hasColumn('booking_settings', 'show_supplementary_info')) {
                    $table->boolean('show_supplementary_info')->default(true);
                }
                if (!Schema::hasColumn('booking_settings', 'show_provider_info')) {
                    $table->boolean('show_provider_info')->default(true);
                }
            });
        }

        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_services', 'description')) {
                    $table->text('description')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('booking_settings', 'show_service_description')) {
                    $columns[] = 'show_service_description';
                }
                if (Schema::hasColumn('booking_settings', 'show_supplementary_info')) {
                    $columns[] = 'show_supplementary_info';
                }
                if (Schema::hasColumn('booking_settings', 'show_provider_info')) {
                    $columns[] = 'show_provider_info';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (Schema::hasColumn('booking_services', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};
