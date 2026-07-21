<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * To modify a column, you must have the doctrine/dbal package installed.
     * composer require doctrine/dbal
     */
    public function up(): void
    {
        Schema::table('service_invoices', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_invoices', function (Blueprint $table) {
            // Note: This might fail if there are multiple NULL entries in the invoice_number column.
            // The user would need to manually clean the data before rolling back this migration.
            $table->string('invoice_number')->nullable(false)->change();
        });
    }
};
