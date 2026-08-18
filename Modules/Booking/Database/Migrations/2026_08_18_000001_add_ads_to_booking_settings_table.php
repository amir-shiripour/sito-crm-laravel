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
        Schema::table('booking_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_settings', 'ads')) {
                $table->json('ads')->nullable()->after('show_provider_info');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            if (Schema::hasColumn('booking_settings', 'ads')) {
                $table->dropColumn('ads');
            }
        });
    }
};
