<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_invoice_items') && Schema::hasColumn('service_invoice_items', 'description')) {
            Schema::table('service_invoice_items', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_invoice_items') && Schema::hasColumn('service_invoice_items', 'description')) {
            try {
                Schema::table('service_invoice_items', function (Blueprint $table) {
                    $table->string('description', 255)->nullable()->change();
                });
            } catch (Throwable $e) {
                // Ignore rollback errors if data exceeds 255 chars
            }
        }
    }
};
