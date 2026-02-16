<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('properties') && !Schema::hasColumn('properties', 'document_type')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->string('document_type')->nullable()->after('property_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'document_type')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('document_type');
            });
        }
    }
};
