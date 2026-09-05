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
        if (Schema::hasTable('treatment_plans')) {
            Schema::table('treatment_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('treatment_plans', 'installment_due_day')) {
                    $table->integer('installment_due_day')->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_start_date')) {
                    $table->string('installment_start_date', 20)->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_interval_months')) {
                    $table->integer('installment_interval_months')->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_down_payment_percent')) {
                    $table->decimal('installment_down_payment_percent', 5, 2)->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_fee_percent')) {
                    $table->decimal('installment_fee_percent', 5, 2)->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_cash_now')) {
                    $table->decimal('installment_cash_now', 15, 2)->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_uncovered_total')) {
                    $table->decimal('installment_uncovered_total', 15, 2)->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_breakdown')) {
                    $table->json('installment_breakdown')->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'generated_cheques')) {
                    $table->json('generated_cheques')->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'tax_value')) {
                    $table->decimal('tax_value', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('treatment_plans', 'final_payable')) {
                    $table->decimal('final_payable', 15, 2)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('treatment_plans')) {
            Schema::table('treatment_plans', function (Blueprint $table) {
                $cols = [
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
                ];
                $toDrop = array_filter($cols, fn($c) => Schema::hasColumn('treatment_plans', $c));
                if (!empty($toDrop)) {
                    $table->dropColumn(array_values($toDrop));
                }
            });
        }
    }
};
