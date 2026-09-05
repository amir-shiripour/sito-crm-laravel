<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('doctor_profiles', 'province')) {
                    $colProvince = $table->string('province', 100)->nullable();
                    if (Schema::hasColumn('doctor_profiles', 'clinic_address')) {
                        $colProvince->after('clinic_address');
                    }
                }
                if (!Schema::hasColumn('doctor_profiles', 'city')) {
                    $colCity = $table->string('city', 100)->nullable();
                    if (Schema::hasColumn('doctor_profiles', 'province')) {
                        $colCity->after('province');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('doctor_profiles', 'province')) {
                    $table->dropColumn('province');
                }
                if (Schema::hasColumn('doctor_profiles', 'city')) {
                    $table->dropColumn('city');
                }
            });
        }
    }
};
