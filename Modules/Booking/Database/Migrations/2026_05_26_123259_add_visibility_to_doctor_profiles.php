<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                // JSON column storing which sections are publicly visible
                // e.g. {"about":true,"insurances":true,"gallery":false,"video":true}
                if (!Schema::hasColumn('doctor_profiles', 'visibility')) {
                    $col = $table->json('visibility')->nullable();
                    if (Schema::hasColumn('doctor_profiles', 'insurances')) {
                        $col->after('insurances');
                    }
                }

                // also add specialty/clinic_address if missing
                if (!Schema::hasColumn('doctor_profiles', 'specialty')) {
                    $colSpecialty = $table->string('specialty')->nullable();
                    if (Schema::hasColumn('doctor_profiles', 'experience')) {
                        $colSpecialty->after('experience');
                    }
                }
                if (!Schema::hasColumn('doctor_profiles', 'clinic_address')) {
                    $colAddress = $table->string('clinic_address')->nullable();
                    if (Schema::hasColumn('doctor_profiles', 'specialty')) {
                        $colAddress->after('specialty');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('doctor_profiles', 'visibility')) {
                    $table->dropColumn('visibility');
                }
            });
        }
    }
};
