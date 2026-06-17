<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('treatment_plans', 'patient_id')) {
                $table->foreignId('patient_id')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null')
                    ->after('user_id');
            }
        });
    }

    public function down()
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            if (Schema::hasColumn('treatment_plans', 'patient_id')) {
                $table->dropForeign(['patient_id']);
                $table->dropColumn('patient_id');
            }
        });
    }
};
