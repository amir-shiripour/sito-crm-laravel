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
            $table->string('installment_option_id')->nullable()->after('total');
            $table->string('installment_option_title')->nullable()->after('installment_option_id');
            $table->decimal('installment_down_payment', 16, 2)->default(0)->after('installment_option_title');
            $table->decimal('installment_monthly_amount', 16, 2)->default(0)->after('installment_down_payment');
            $table->decimal('installment_fee_value', 16, 2)->default(0)->after('installment_monthly_amount');
            $table->integer('installment_months')->default(0)->after('installment_fee_value');
            $table->integer('installment_count')->default(0)->after('installment_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dropColumn([
                'installment_option_id',
                'installment_option_title',
                'installment_down_payment',
                'installment_monthly_amount',
                'installment_fee_value',
                'installment_months',
                'installment_count',
            ]);
        });
    }
};
