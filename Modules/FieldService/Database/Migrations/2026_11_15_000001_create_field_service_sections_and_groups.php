<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('field_service_sections')) {
            Schema::create('field_service_sections', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('field_service_service_groups')) {
            Schema::create('field_service_service_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('section_id')
                    ->constrained('field_service_sections')
                    ->restrictOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('field_service_categories') && ! Schema::hasColumn('field_service_categories', 'group_id')) {
            Schema::table('field_service_categories', function (Blueprint $table) {
                $table->foreignId('group_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('field_service_service_groups')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('field_service_categories') && Schema::hasColumn('field_service_categories', 'group_id')) {
            Schema::table('field_service_categories', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            });
        }

        Schema::dropIfExists('field_service_service_groups');
        Schema::dropIfExists('field_service_sections');
    }
};
