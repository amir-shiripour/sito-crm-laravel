<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('doctor_profiles', 'specialty')) {
                    $table->text('specialty')->nullable()->change();
                }
                if (Schema::hasColumn('doctor_profiles', 'education')) {
                    $table->text('education')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('doctor_profiles')) {
            Schema::table('doctor_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('doctor_profiles', 'specialty')) {
                    $table->string('specialty', 191)->nullable()->change();
                }
                if (Schema::hasColumn('doctor_profiles', 'education')) {
                    $table->string('education', 191)->nullable()->change();
                }
            });
        }
    }
};
