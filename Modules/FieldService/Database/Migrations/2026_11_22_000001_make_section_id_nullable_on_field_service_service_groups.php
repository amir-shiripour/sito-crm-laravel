<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('field_service_service_groups') && Schema::hasColumn('field_service_service_groups', 'section_id')) {
            Schema::table('field_service_service_groups', function (Blueprint $table) {
                $table->dropForeign(['section_id']);
            });

            Schema::table('field_service_service_groups', function (Blueprint $table) {
                $table->unsignedBigInteger('section_id')->nullable()->change();
                $table->foreign('section_id')
                    ->references('id')
                    ->on('field_service_sections')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('field_service_service_groups') && Schema::hasColumn('field_service_service_groups', 'section_id')) {
            Schema::table('field_service_service_groups', function (Blueprint $table) {
                $table->dropForeign(['section_id']);
            });

            Schema::table('field_service_service_groups', function (Blueprint $table) {
                $table->unsignedBigInteger('section_id')->nullable(false)->change();
                $table->foreign('section_id')
                    ->references('id')
                    ->on('field_service_sections')
                    ->restrictOnDelete();
            });
        }
    }
};
