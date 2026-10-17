<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_categories')) {
            Schema::create('field_service_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->string('icon', 50)->nullable();
                $table->string('appliance_type', 40)->default('other');
                $table->decimal('base_labor_fee', 14, 2)->default(0); // in IRR
                $table->unsignedInteger('warranty_days')->default(90);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('field_service_technician_skills')) {
            Schema::create('field_service_technician_skills', function (Blueprint $table) {
                $table->id();
                $table->foreignId('technician_id')->constrained('field_service_technicians')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('field_service_categories')->cascadeOnDelete();
                $table->boolean('is_verified')->default(false);
                $table->string('certificate_number', 100)->nullable();
                $table->unsignedTinyInteger('experience_years')->default(1);
                $table->timestamps();

                $table->unique(['technician_id', 'category_id'], 'tech_cat_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_technician_skills');
        Schema::dropIfExists('field_service_categories');
    }
};
