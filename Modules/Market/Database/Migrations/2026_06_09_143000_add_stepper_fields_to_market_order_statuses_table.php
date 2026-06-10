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
        Schema::table('market_order_statuses', function (Blueprint $table) {
            $table->boolean('show_to_client')->default(true)->after('system_type');
            $table->boolean('show_in_client_stepper')->default(true)->after('show_to_client');
            $table->boolean('show_in_admin_stepper')->default(true)->after('show_in_client_stepper');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_order_statuses', function (Blueprint $table) {
            $table->dropColumn(['show_to_client', 'show_in_client_stepper', 'show_in_admin_stepper']);
        });
    }
};
