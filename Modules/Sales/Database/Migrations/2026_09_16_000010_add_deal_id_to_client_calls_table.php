<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_calls') && !Schema::hasColumn('client_calls', 'deal_id')) {
            Schema::table('client_calls', function (Blueprint $table) {
                $table->unsignedBigInteger('deal_id')->nullable()->after('campaign_id');
                if (Schema::hasTable('sales_deals')) {
                    $table->foreign('deal_id')->references('id')->on('sales_deals')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('client_calls') && Schema::hasColumn('client_calls', 'deal_id')) {
            Schema::table('client_calls', function (Blueprint $table) {
                $table->dropForeign(['deal_id']);
                $table->dropColumn('deal_id');
            });
        }
    }
};
