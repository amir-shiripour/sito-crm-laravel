<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_categories') && !Schema::hasColumn('service_categories', 'is_locked')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->boolean('is_locked')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_categories') && Schema::hasColumn('service_categories', 'is_locked')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropColumn('is_locked');
            });
        }
    }
};
