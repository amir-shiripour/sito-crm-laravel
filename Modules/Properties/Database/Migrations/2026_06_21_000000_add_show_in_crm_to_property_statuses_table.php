<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_statuses') && !Schema::hasColumn('property_statuses', 'show_in_crm')) {
            Schema::table('property_statuses', function (Blueprint $table) {
                $table->boolean('show_in_crm')->default(true)->after('is_default');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('property_statuses') && Schema::hasColumn('property_statuses', 'show_in_crm')) {
            Schema::table('property_statuses', function (Blueprint $table) {
                $table->dropColumn('show_in_crm');
            });
        }
    }
};
