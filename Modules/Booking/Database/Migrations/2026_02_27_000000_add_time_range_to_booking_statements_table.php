<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeRangeToBookingStatementsTable extends Migration
{
    public function up()
    {
        Schema::table('booking_statements', function (Blueprint $table) {
            $table->time('first_appointment_time')->nullable()->after('end_date');
            $table->time('last_appointment_time')->nullable()->after('first_appointment_time');
        });
    }

    public function down()
    {
        Schema::table('booking_statements', function (Blueprint $table) {
            $table->dropColumn(['first_appointment_time', 'last_appointment_time']);
        });
    }
}
