<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_calls', function (Blueprint $table) {
            $table->foreignId('deal_id')
                ->nullable()
                ->after('campaign_id')
                ->constrained('sales_deals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_calls', function (Blueprint $table) {
            $table->dropForeign(['deal_id']);
            $table->dropColumn('deal_id');
        });
    }
};
