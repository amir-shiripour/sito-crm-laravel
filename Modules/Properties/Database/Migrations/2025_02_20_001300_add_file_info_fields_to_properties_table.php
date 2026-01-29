<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->date('registered_at')->nullable()->after('building_id');
            $table->string('publication_status')->default('published')->after('registered_at');
            $table->text('confidential_notes')->nullable()->after('publication_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['registered_at', 'publication_status', 'confidential_notes']);
        });
    }
};
