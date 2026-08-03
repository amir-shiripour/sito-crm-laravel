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
        Schema::table('service_invoices', function (Blueprint $table) {
            $table->string('issue_date', 20)->nullable()->change();
            $table->string('due_date', 20)->nullable()->change();
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('renewal_date', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_invoices', function (Blueprint $table) {
            $table->date('issue_date')->nullable()->change();
            $table->date('due_date')->nullable()->change();
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->date('renewal_date')->nullable()->change();
        });
    }
};
