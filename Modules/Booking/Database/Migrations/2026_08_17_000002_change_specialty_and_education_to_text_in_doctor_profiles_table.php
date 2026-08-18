<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->text('specialty')->nullable()->change();
            $table->text('education')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->string('specialty', 191)->nullable()->change();
            $table->string('education', 191)->nullable()->change();
        });
    }
};
