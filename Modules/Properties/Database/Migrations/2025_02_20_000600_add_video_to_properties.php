<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('properties') && !Schema::hasColumn('properties', 'video')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->string('video')->nullable()->after('cover_image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'video')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('video');
            });
        }
    }
};
