<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_orders')) {
            Schema::create('field_service_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 40)->unique()->index(); // e.g. NB-1405-00128
                
                // Strictly connected to Clients module
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
                
                // Technician assignment
                $table->foreignId('technician_id')->nullable()->constrained('field_service_technicians')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('field_service_categories')->nullOnDelete();
                
                // Optional B2B associations
                $table->foreignId('building_id')->nullable()->constrained('field_service_buildings')->nullOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained('field_service_vendors')->nullOnDelete();
                
                // Device and problem details
                $table->string('appliance_type', 50)->default('package');
                $table->string('appliance_brand_model', 100)->nullable(); // e.g. بوتان پارما ۲۴ / ایران رادیاتور L24FF
                $table->string('serial_number', 100)->nullable();
                $table->text('problem_description')->nullable();
                
                // Status and priority
                $table->string('status', 30)->default('pending')->index(); // OrderStatus enum
                $table->string('priority', 20)->default('normal'); // normal, urgent, emergency
                $table->boolean('is_emergency')->default(false)->index();
                
                // Location details
                $table->string('province', 60)->default('مازندران');
                $table->string('city', 60)->default('بابل');
                $table->string('district', 100)->nullable();
                $table->text('address');
                $table->decimal('lat', 10, 7)->nullable()->index();
                $table->decimal('lng', 10, 7)->nullable()->index();
                
                // Contact info
                $table->string('customer_name', 100);
                $table->string('customer_phone', 20)->index();
                
                // Dynamic Radius & Dispatch tracking
                $table->unsignedInteger('search_radius_km')->default(3); // 3, 7, 15, 30, 60 km
                $table->unsignedTinyInteger('search_expanded_count')->default(0);
                $table->timestamp('dispatched_at')->nullable();
                $table->unsignedSmallInteger('dispatch_timeout_seconds')->default(45);
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('en_route_at')->nullable();
                $table->timestamp('arrived_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->string('cancellation_reason')->nullable();
                
                $table->text('notes')->nullable();
                $table->json('meta')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_orders');
    }
};
