<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_buildings')) {
            Schema::create('field_service_buildings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->string('name', 150); // e.g. برج مسکونی یاسمن بابل
                $table->string('building_type', 40)->default('residential'); // residential, commercial, industrial, complex
                $table->unsignedInteger('units_count')->default(1);
                $table->unsignedInteger('floors_count')->default(1);
                $table->string('manager_name', 100)->nullable();
                $table->string('manager_phone', 30)->nullable();
                $table->string('province', 60)->default('مازندران');
                $table->string('city', 60)->default('بابل');
                $table->text('address')->nullable();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('field_service_building_equipments')) {
            Schema::create('field_service_building_equipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('building_id')->constrained('field_service_buildings')->cascadeOnDelete();
                $table->string('equipment_type', 50); // boiler_room, absorption_chiller, booster_pump, elevator, generator
                $table->string('title', 120); // e.g. چیلر جذبی سنچوری ۳۰۰ تن
                $table->string('brand_model', 100)->nullable();
                $table->string('serial_number', 100)->nullable();
                $table->string('installation_year', 10)->nullable();
                $table->string('health_status', 30)->default('good'); // good, needs_inspection, critical
                $table->timestamp('last_serviced_at')->nullable();
                $table->timestamp('next_service_due')->nullable();
                $table->json('specifications')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('field_service_sla_contracts')) {
            Schema::create('field_service_sla_contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('building_id')->constrained('field_service_buildings')->cascadeOnDelete();
                $table->string('contract_number', 50)->unique();
                $table->string('title', 150); // e.g. قرارداد طلایی پایش و نگهداری پیشگیرانه
                $table->string('tier', 30)->default('gold_sla'); // bronze, silver, gold_sla
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('monthly_fee', 14, 2)->default(0); // in IRR
                $table->unsignedTinyInteger('emergency_response_hours')->default(2); // زمان پاسخدهی اضطراری
                $table->unsignedTinyInteger('periodic_visits_per_month')->default(2);
                $table->string('status', 30)->default('active'); // active, expired, suspended
                $table->text('terms')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_sla_contracts');
        Schema::dropIfExists('field_service_building_equipments');
        Schema::dropIfExists('field_service_buildings');
    }
};
