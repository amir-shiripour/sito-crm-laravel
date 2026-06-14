<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_master_products', function (Blueprint $table) {
            $table->boolean('enable_questions')->default(true)->after('enable_reviews');
        });
    }

    public function down(): void
    {
        Schema::table('market_master_products', function (Blueprint $table) {
            $table->dropColumn('enable_questions');
        });
    }
};
