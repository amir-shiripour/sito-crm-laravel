<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_invoices', function (Blueprint $table) {
            // Only add the columns that are missing from your original migration
            if (!Schema::hasColumn('service_invoices', 'payment_mode')) {
                $table->string('payment_mode')->nullable()->after('currency');
            }

            if (!Schema::hasColumn('service_invoices', 'installment_down_payment')) {
                $table->integer('installment_down_payment')->nullable()->after('payment_gateway');
            }

            if (!Schema::hasColumn('service_invoices', 'installment_steps')) {
                $table->integer('installment_steps')->nullable()->after('installment_down_payment');
            }

            if (!Schema::hasColumn('service_invoices', 'installment_interest_rate')) {
                $table->decimal('installment_interest_rate', 5, 2)->nullable()->after('installment_steps');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'installment_down_payment', 'installment_steps', 'installment_interest_rate']);
        });
    }
};
