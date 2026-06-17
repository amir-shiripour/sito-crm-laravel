<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            // JSON column storing which sections are publicly visible
            // e.g. {"about":true,"insurances":true,"gallery":false,"video":true}
            $table->json('visibility')->nullable()->after('insurances');

            // also add specialty/clinic_address if missing
            if (!Schema::hasColumn('doctor_profiles', 'specialty')) {
                $table->string('specialty')->nullable()->after('experience');
            }
            if (!Schema::hasColumn('doctor_profiles', 'clinic_address')) {
                $table->string('clinic_address')->nullable()->after('specialty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
