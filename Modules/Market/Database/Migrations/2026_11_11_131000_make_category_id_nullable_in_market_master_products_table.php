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
        if (Schema::hasTable('market_master_products')) {
            Schema::table('market_master_products', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('market_master_products')) {
            Schema::table('market_master_products', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable(false)->change();
            });
        }
    }
};
