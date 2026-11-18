<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('field_service_categories') && ! Schema::hasColumn('field_service_categories', 'section_id')) {
            Schema::table('field_service_categories', function (Blueprint $table) {
                $table->foreignId('section_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('field_service_sections')
                    ->nullOnDelete();
            });

            if (Schema::hasTable('field_service_service_groups')) {
                DB::statement('UPDATE field_service_categories c INNER JOIN field_service_service_groups g ON c.group_id = g.id SET c.section_id = g.section_id WHERE c.group_id IS NOT NULL AND c.section_id IS NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('field_service_categories') && Schema::hasColumn('field_service_categories', 'section_id')) {
            Schema::table('field_service_categories', function (Blueprint $table) {
                $table->dropForeign(['section_id']);
                $table->dropColumn('section_id');
            });
        }
    }
};
