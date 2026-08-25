<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_waitlists')) {
            Schema::table('booking_waitlists', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_waitlists', 'duration_minutes')) {
                    $table->unsignedInteger('duration_minutes')->nullable()->after('preferred_date');
                }
                if (!Schema::hasColumn('booking_waitlists', 'appointment_form_response_json')) {
                    $table->json('appointment_form_response_json')->nullable()->after('notes');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_waitlists')) {
            Schema::table('booking_waitlists', function (Blueprint $table) {
                if (Schema::hasColumn('booking_waitlists', 'duration_minutes')) {
                    $table->dropColumn('duration_minutes');
                }
                if (Schema::hasColumn('booking_waitlists', 'appointment_form_response_json')) {
                    $table->dropColumn('appointment_form_response_json');
                }
            });
        }
    }
};
