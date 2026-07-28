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
        Schema::table('market_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('market_orders', 'source_invoice_id')) {
                $table->unsignedBigInteger('source_invoice_id')->nullable()->after('id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_orders', function (Blueprint $table) {
            if (Schema::hasColumn('market_orders', 'source_invoice_id')) {
                $table->dropColumn('source_invoice_id');
            }
        });
    }
};
