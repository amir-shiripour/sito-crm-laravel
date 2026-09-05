<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_statements')) {
            Schema::table('booking_statements', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_statements', 'first_appointment_time')) {
                    $table->time('first_appointment_time')->nullable();
                }
                if (!Schema::hasColumn('booking_statements', 'last_appointment_time')) {
                    $table->time('last_appointment_time')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('booking_statements')) {
            Schema::table('booking_statements', function (Blueprint $table) {
                $cols = ['first_appointment_time', 'last_appointment_time'];
                $toDrop = array_filter($cols, fn($c) => Schema::hasColumn('booking_statements', $c));
                if (!empty($toDrop)) {
                    $table->dropColumn(array_values($toDrop));
                }
            });
        }
    }
};
