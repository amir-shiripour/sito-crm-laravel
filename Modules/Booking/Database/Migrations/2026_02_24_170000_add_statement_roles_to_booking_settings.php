<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->json('statement_roles')->nullable()->after('allowed_roles');
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->dropColumn('statement_roles');
        });
    }
};
