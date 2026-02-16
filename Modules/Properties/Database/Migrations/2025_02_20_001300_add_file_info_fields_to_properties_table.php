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
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (!Schema::hasColumn('properties', 'registered_at')) {
                    $table->date('registered_at')->nullable()->after('building_id');
                }
                if (!Schema::hasColumn('properties', 'publication_status')) {
                    $table->string('publication_status')->default('published')->after('registered_at');
                }
                if (!Schema::hasColumn('properties', 'confidential_notes')) {
                    $table->text('confidential_notes')->nullable()->after('publication_status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn(['registered_at', 'publication_status', 'confidential_notes']);
            });
        }
    }
};
