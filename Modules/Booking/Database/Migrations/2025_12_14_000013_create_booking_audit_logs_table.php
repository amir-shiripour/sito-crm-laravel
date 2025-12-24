<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_service_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_service_providers', 'override_category_id')) {
                $table->unsignedBigInteger('override_category_id')->nullable()->after('override_discount_to');
                $table->index('override_category_id');
            }

            if (!Schema::hasColumn('booking_service_providers', 'override_appointment_form_id')) {
                $table->unsignedBigInteger('override_appointment_form_id')->nullable()->after('override_category_id');
                $table->index('override_appointment_form_id');
            }

            if (!Schema::hasColumn('booking_service_providers', 'override_payment_mode')) {
                $table->string('override_payment_mode', 32)->nullable()->after('override_online_booking_mode');
            }

            if (!Schema::hasColumn('booking_service_providers', 'override_payment_amount_type')) {
                $table->string('override_payment_amount_type', 32)->nullable()->after('override_payment_mode');
            }

            if (!Schema::hasColumn('booking_service_providers', 'override_payment_amount_value')) {
                $table->decimal('override_payment_amount_value', 14, 2)->nullable()->after('override_payment_amount_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_service_providers', function (Blueprint $table) {
            if (Schema::hasColumn('booking_service_providers', 'override_payment_amount_value')) {
                $table->dropColumn('override_payment_amount_value');
            }
            if (Schema::hasColumn('booking_service_providers', 'override_payment_amount_type')) {
                $table->dropColumn('override_payment_amount_type');
            }
            if (Schema::hasColumn('booking_service_providers', 'override_payment_mode')) {
                $table->dropColumn('override_payment_mode');
            }

            if (Schema::hasColumn('booking_service_providers', 'override_appointment_form_id')) {
                $table->dropIndex(['override_appointment_form_id']);
                $table->dropColumn('override_appointment_form_id');
            }

            if (Schema::hasColumn('booking_service_providers', 'override_category_id')) {
                $table->dropIndex(['override_category_id']);
                $table->dropColumn('override_category_id');
            }
        });
    }
};
