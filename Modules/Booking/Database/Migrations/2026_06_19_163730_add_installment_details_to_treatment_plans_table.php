<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->integer('installment_due_day')->nullable()->after('installment_count');
            $table->string('installment_start_date', 20)->nullable()->after('installment_due_day');
            $table->integer('installment_interval_months')->nullable()->after('installment_start_date');
            $table->decimal('installment_down_payment_percent', 5, 2)->nullable()->after('installment_interval_months');
            $table->decimal('installment_fee_percent', 5, 2)->nullable()->after('installment_down_payment_percent');
            $table->decimal('installment_cash_now', 15, 2)->nullable()->after('installment_fee_percent');
            $table->decimal('installment_uncovered_total', 15, 2)->nullable()->after('installment_cash_now');
            $table->json('installment_breakdown')->nullable()->after('installment_uncovered_total');
            $table->json('generated_cheques')->nullable()->after('installment_breakdown');
            $table->decimal('tax_value', 15, 2)->default(0)->after('discount_value');
            $table->decimal('final_payable', 15, 2)->nullable()->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dropColumn([
                'installment_due_day',
                'installment_start_date',
                'installment_interval_months',
                'installment_down_payment_percent',
                'installment_fee_percent',
                'installment_cash_now',
                'installment_uncovered_total',
                'installment_breakdown',
                'generated_cheques',
                'tax_value',
                'final_payable',
            ]);
        });
    }
};
