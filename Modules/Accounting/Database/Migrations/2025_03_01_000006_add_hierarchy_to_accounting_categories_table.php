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
        Schema::table('accounting_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('accounting_categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('accounting_categories', 'account_code')) {
                $table->string('account_code', 20)->nullable()->unique()->after('title');
            }
            if (!Schema::hasColumn('accounting_categories', 'level')) {
                $table->integer('level')->default(1)->after('account_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('accounting_categories', 'parent_id')) {
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE `accounting_categories` DROP FOREIGN KEY `accounting_categories_parent_id_foreign`');
            } catch (\Throwable $e) {}
        }

        Schema::table('accounting_categories', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['parent_id', 'account_code', 'level'] as $col) {
                if (Schema::hasColumn('accounting_categories', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
