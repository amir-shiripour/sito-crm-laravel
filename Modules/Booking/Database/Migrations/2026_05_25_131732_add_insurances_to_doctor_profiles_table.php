<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('doctor_profiles', 'insurances')) {
                    $table->json('insurances')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('doctor_profiles', 'insurances')) {
                    $table->dropColumn('insurances');
                }
            });
        }
    }
};
