<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('service_invoices', 'installment_option_id')) {
                $table->string('installment_option_id')->nullable()->after('payment_mode');
            }
            if (!Schema::hasColumn('service_invoices', 'installment_option_title')) {
                $table->string('installment_option_title')->nullable()->after('installment_option_id');
            }
            if (!Schema::hasColumn('service_invoices', 'installment_due_day')) {
                $table->unsignedTinyInteger('installment_due_day')->nullable()->after('installment_interest_rate');
            }
            if (!Schema::hasColumn('service_invoices', 'installment_start_date')) {
                $table->string('installment_start_date')->nullable()->after('installment_due_day');
            }
            if (!Schema::hasColumn('service_invoices', 'installment_schedule')) {
                $table->json('installment_schedule')->nullable()->after('installment_start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'installment_option_id',
                'installment_option_title',
                'installment_due_day',
                'installment_start_date',
                'installment_schedule',
            ]);
        });
    }
};
