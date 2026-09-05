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
                if (!Schema::hasColumn('treatment_plans', 'installment_option_id')) {
                    $table->string('installment_option_id')->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_option_title')) {
                    $table->string('installment_option_title')->nullable();
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_down_payment')) {
                    $table->decimal('installment_down_payment', 16, 2)->default(0);
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_monthly_amount')) {
                    $table->decimal('installment_monthly_amount', 16, 2)->default(0);
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_fee_value')) {
                    $table->decimal('installment_fee_value', 16, 2)->default(0);
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_months')) {
                    $table->integer('installment_months')->default(0);
                }
                if (!Schema::hasColumn('treatment_plans', 'installment_count')) {
                    $table->integer('installment_count')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('treatment_plans')) {
            Schema::table('treatment_plans', function (Blueprint $table) {
                $cols = [
                    'installment_option_id',
                    'installment_option_title',
                    'installment_down_payment',
                    'installment_monthly_amount',
                    'installment_fee_value',
                    'installment_months',
                    'installment_count',
                ];
                $toDrop = array_filter($cols, fn($c) => Schema::hasColumn('treatment_plans', $c));
                if (!empty($toDrop)) {
                    $table->dropColumn(array_values($toDrop));
                }
            });
        }
    }
};
