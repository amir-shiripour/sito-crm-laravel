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
        if (!Schema::hasColumn('services', 'accounting_category_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->unsignedBigInteger('accounting_category_id')->nullable()->after('category_id');

                if (Schema::hasTable('accounting_categories')) {
                    $table->foreign('accounting_category_id')
                        ->references('id')
                        ->on('accounting_categories')
                        ->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('services', 'accounting_category_id')) {
            Schema::table('services', function (Blueprint $table) {
                if (Schema::hasTable('accounting_categories')) {
                    try {
                        $table->dropForeign(['accounting_category_id']);
                    } catch (\Throwable $e) {
                        // Ignore if FK doesn't exist
                    }
                }
                $table->dropColumn('accounting_category_id');
            });
        }
    }
};
