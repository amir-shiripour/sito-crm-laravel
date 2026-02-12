<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('created_by');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
        });

        // Migrate existing data: set agent_id = created_by initially
        \Illuminate\Support\Facades\DB::statement('UPDATE properties SET agent_id = created_by');
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
        });
    }
};
