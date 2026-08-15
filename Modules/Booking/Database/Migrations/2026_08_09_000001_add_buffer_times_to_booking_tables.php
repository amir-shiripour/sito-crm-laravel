<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_settings', 'default_buffer_before_minutes')) {
                    $table->unsignedInteger('default_buffer_before_minutes')->nullable()->after('default_capacity_per_day');
                }
                if (!Schema::hasColumn('booking_settings', 'default_buffer_after_minutes')) {
                    $table->unsignedInteger('default_buffer_after_minutes')->nullable()->after('default_buffer_before_minutes');
                }
            });
        }

        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_services', 'buffer_before_minutes')) {
                    $table->unsignedInteger('buffer_before_minutes')->nullable()->after('custom_schedule_enabled');
                }
                if (!Schema::hasColumn('booking_services', 'buffer_after_minutes')) {
                    $table->unsignedInteger('buffer_after_minutes')->nullable()->after('buffer_before_minutes');
                }
            });
        }

        if (Schema::hasTable('booking_availability_rules')) {
            Schema::table('booking_availability_rules', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_availability_rules', 'buffer_before_minutes')) {
                    $table->unsignedInteger('buffer_before_minutes')->nullable()->after('capacity_per_day');
                }
                if (!Schema::hasColumn('booking_availability_rules', 'buffer_after_minutes')) {
                    $table->unsignedInteger('buffer_after_minutes')->nullable()->after('buffer_before_minutes');
                }
            });
        }

        if (Schema::hasTable('booking_availability_exceptions')) {
            Schema::table('booking_availability_exceptions', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_availability_exceptions', 'override_buffer_before_minutes')) {
                    $table->unsignedInteger('override_buffer_before_minutes')->nullable()->after('override_capacity_per_day');
                }
                if (!Schema::hasColumn('booking_availability_exceptions', 'override_buffer_after_minutes')) {
                    $table->unsignedInteger('override_buffer_after_minutes')->nullable()->after('override_buffer_before_minutes');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (Schema::hasColumn('booking_settings', 'default_buffer_before_minutes')) {
                    $table->dropColumn(['default_buffer_before_minutes', 'default_buffer_after_minutes']);
                }
            });
        }

        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (Schema::hasColumn('booking_services', 'buffer_before_minutes')) {
                    $table->dropColumn(['buffer_before_minutes', 'buffer_after_minutes']);
                }
            });
        }

        if (Schema::hasTable('booking_availability_rules')) {
            Schema::table('booking_availability_rules', function (Blueprint $table) {
                if (Schema::hasColumn('booking_availability_rules', 'buffer_before_minutes')) {
                    $table->dropColumn(['buffer_before_minutes', 'buffer_after_minutes']);
                }
            });
        }

        if (Schema::hasTable('booking_availability_exceptions')) {
            Schema::table('booking_availability_exceptions', function (Blueprint $table) {
                if (Schema::hasColumn('booking_availability_exceptions', 'override_buffer_before_minutes')) {
                    $table->dropColumn(['override_buffer_before_minutes', 'override_buffer_after_minutes']);
                }
            });
        }
    }
};
