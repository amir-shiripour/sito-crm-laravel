<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_technicians')) {
            Schema::create('field_service_technicians', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('mobile', 20)->unique()->index();
                $table->string('national_code', 20)->nullable()->unique();
                $table->string('shaba_number', 40)->nullable(); // IR...
                $table->string('card_number', 30)->nullable();
                $table->string('avatar')->nullable();
                
                // Location & operational zone
                $table->string('province', 60)->default('مازندران');
                $table->string('city', 60)->default('بابل');
                $table->text('address')->nullable();
                $table->decimal('lat', 10, 7)->nullable()->index();
                $table->decimal('lng', 10, 7)->nullable()->index();
                $table->unsignedInteger('operational_radius_km')->default(25);
                
                // Availability & status
                $table->boolean('is_online')->default(false)->index();
                $table->boolean('is_busy')->default(false)->index();
                $table->string('status', 30)->default('pending_approval')->index(); // pending_approval, active, suspended, inactive
                $table->string('tier', 20)->default('silver'); // silver, gold, master
                
                // Statistics & scoring
                $table->decimal('rating_avg', 3, 2)->default(5.00);
                $table->unsignedInteger('rating_count')->default(0);
                $table->unsignedInteger('completed_orders_count')->default(0);
                $table->decimal('acceptance_rate', 5, 2)->default(100.00);
                $table->timestamp('last_active_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                
                $table->json('meta')->nullable(); // certificates, vehicle info, tools list
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_technicians');
    }
};
