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
        if (Schema::hasTable('accounting_invoice_items') && !Schema::hasColumn('accounting_invoice_items', 'category_id')) {
            Schema::table('accounting_invoice_items', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('invoice_id')
                    ->constrained('accounting_categories')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('accounting_invoice_items') && Schema::hasColumn('accounting_invoice_items', 'category_id')) {
            Schema::table('accounting_invoice_items', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
};
