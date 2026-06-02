<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_order_meta', function (Blueprint $table) {
            $table->string('label')->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('market_order_meta', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
