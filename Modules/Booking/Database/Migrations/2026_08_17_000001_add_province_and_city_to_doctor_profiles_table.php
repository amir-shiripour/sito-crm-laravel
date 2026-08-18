<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('doctor_profiles', 'province')) {
                $table->string('province', 100)->nullable()->after('clinic_address');
            }
            if (!Schema::hasColumn('doctor_profiles', 'city')) {
                $table->string('city', 100)->nullable()->after('province');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('doctor_profiles', 'province')) {
                $table->dropColumn('province');
            }
            if (Schema::hasColumn('doctor_profiles', 'city')) {
                $table->dropColumn('city');
            }
        });
    }
};
