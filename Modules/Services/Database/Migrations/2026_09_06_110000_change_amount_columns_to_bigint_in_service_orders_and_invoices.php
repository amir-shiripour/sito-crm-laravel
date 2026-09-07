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
        if (Schema::hasTable('service_orders')) {
            Schema::table('service_orders', function (Blueprint $table) {
                if (Schema::hasColumn('service_orders', 'first_payment_amount')) {
                    $table->unsignedBigInteger('first_payment_amount')->default(0)->change();
                }
                if (Schema::hasColumn('service_orders', 'renewal_price')) {
                    $table->unsignedBigInteger('renewal_price')->default(0)->change();
                }
                if (Schema::hasColumn('service_orders', 'total_amount')) {
                    $table->unsignedBigInteger('total_amount')->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('service_invoices')) {
            Schema::table('service_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('service_invoices', 'installment_down_payment')) {
                    $table->unsignedBigInteger('installment_down_payment')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('service_orders')) {
            Schema::table('service_orders', function (Blueprint $table) {
                if (Schema::hasColumn('service_orders', 'first_payment_amount')) {
                    $table->integer('first_payment_amount')->default(0)->change();
                }
                if (Schema::hasColumn('service_orders', 'renewal_price')) {
                    $table->integer('renewal_price')->default(0)->change();
                }
                if (Schema::hasColumn('service_orders', 'total_amount')) {
                    $table->integer('total_amount')->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('service_invoices')) {
            Schema::table('service_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('service_invoices', 'installment_down_payment')) {
                    $table->integer('installment_down_payment')->nullable()->change();
                }
            });
        }
    }
};
