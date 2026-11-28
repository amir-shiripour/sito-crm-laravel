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
        Schema::table('warehouse_transfer_requests', function (Blueprint $table) {
            $table->date('needed_date')->nullable()->after('request_date')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_transfer_requests', function (Blueprint $table) {
            $table->dropIndex(['needed_date']);
            $table->dropColumn('needed_date');
        });
    }
};
