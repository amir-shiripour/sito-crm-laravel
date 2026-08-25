<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_invoice_items') && Schema::hasColumn('service_invoice_items', 'description')) {
            try {
                Schema::table('service_invoice_items', function (Blueprint $table) {
                    $table->text('description')->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Column is already nullable or modification handled
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_invoice_items') && Schema::hasColumn('service_invoice_items', 'description')) {
            try {
                Schema::table('service_invoice_items', function (Blueprint $table) {
                    $table->string('description')->nullable(false)->change();
                });
            } catch (\Throwable $e) {
                // Prevent error on rollback if table contains null descriptions
            }
        }
    }
};
