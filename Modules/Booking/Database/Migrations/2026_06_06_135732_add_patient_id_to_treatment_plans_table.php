<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('treatment_plans')) {
            Schema::table('treatment_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('treatment_plans', 'patient_id')) {
                    $col = $table->foreignId('patient_id')
                        ->nullable()
                        ->constrained('users')
                        ->onDelete('set null');
                    if (Schema::hasColumn('treatment_plans', 'user_id')) {
                        $col->after('user_id');
                    }
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('treatment_plans')) {
            Schema::table('treatment_plans', function (Blueprint $table) {
                if (Schema::hasColumn('treatment_plans', 'patient_id')) {
                    $table->dropForeign(['patient_id']);
                    $table->dropColumn('patient_id');
                }
            });
        }
    }
};
