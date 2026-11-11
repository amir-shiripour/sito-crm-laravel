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
        if (Schema::hasTable('market_brands') && !Schema::hasColumn('market_brands', 'vision_code')) {
            Schema::table('market_brands', function (Blueprint $table) {
                $table->string('vision_code', 50)->nullable()->index()->after('code_prefix');
            });
        }

        if (Schema::hasTable('market_categories') && !Schema::hasColumn('market_categories', 'vision_code')) {
            Schema::table('market_categories', function (Blueprint $table) {
                $table->string('vision_code', 50)->nullable()->index()->after('code_offset');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('market_brands') && Schema::hasColumn('market_brands', 'vision_code')) {
            Schema::table('market_brands', function (Blueprint $table) {
                $table->dropColumn('vision_code');
            });
        }

        if (Schema::hasTable('market_categories') && Schema::hasColumn('market_categories', 'vision_code')) {
            Schema::table('market_categories', function (Blueprint $table) {
                $table->dropColumn('vision_code');
            });
        }
    }
};
