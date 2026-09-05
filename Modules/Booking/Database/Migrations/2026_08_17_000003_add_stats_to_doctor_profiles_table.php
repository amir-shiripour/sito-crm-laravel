<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('doctor_profiles', 'stats')) {
                    $col = $table->json('stats')->nullable();
                    if (Schema::hasColumn('doctor_profiles', 'visibility')) {
                        $col->after('visibility');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('doctor_profiles', 'stats')) {
                    $table->dropColumn('stats');
                }
            });
        }
    }
};
