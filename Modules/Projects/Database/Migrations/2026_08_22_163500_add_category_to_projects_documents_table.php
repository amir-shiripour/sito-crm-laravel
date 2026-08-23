<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('projects_documents', 'category')) {
                $table->string('category', 100)->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects_documents', function (Blueprint $table) {
            if (Schema::hasColumn('projects_documents', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
