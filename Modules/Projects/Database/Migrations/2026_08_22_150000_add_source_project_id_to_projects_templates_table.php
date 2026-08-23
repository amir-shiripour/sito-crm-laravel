<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects_templates', function (Blueprint $table) {
            $table->foreignId('source_project_id')
                ->nullable()
                ->after('category_id')
                ->constrained('projects')
                ->nullOnDelete();
        });

        // Link existing template ID 2 to Project ID 2 if exists
        try {
            DB::table('projects_templates')->where('id', 2)->update(['source_project_id' => 2]);
        } catch (\Throwable) {
            // Ignore if doesn't exist
        }
    }

    public function down(): void
    {
        Schema::table('projects_templates', function (Blueprint $table) {
            $table->dropForeign(['source_project_id']);
            $table->dropColumn('source_project_id');
        });
    }
};
