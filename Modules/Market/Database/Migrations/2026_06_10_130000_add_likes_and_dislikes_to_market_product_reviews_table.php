<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_product_reviews', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('comment');
            $table->unsignedInteger('dislikes_count')->default(0)->after('likes_count');
        });
    }

    public function down(): void
    {
        Schema::table('market_product_reviews', function (Blueprint $table) {
            $table->dropColumn(['likes_count', 'dislikes_count']);
        });
    }
};
