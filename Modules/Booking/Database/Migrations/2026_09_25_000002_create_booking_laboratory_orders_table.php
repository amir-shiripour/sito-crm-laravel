<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('booking_laboratory_orders')) {
            Schema::create('booking_laboratory_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 50)->unique();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->string('patient_name')->index();
                $table->string('patient_file_number', 50)->nullable()->index();
                $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
                $table->foreignId('treatment_plan_id')->nullable()->constrained('treatment_plans')->nullOnDelete();
                $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
                
                $table->string('lab_type', 30)->default('external')->index(); // 'external', 'in_house'
                $table->string('lab_partner_name', 100)->nullable()->index();
                $table->string('category_type', 50)->default('units_1_2')->index();
                $table->string('full_jaw_phase', 50)->nullable(); // 'base_rim', 'pmma', 'porcelain_try_in', 'final_glaze'
                $table->boolean('has_pmma')->default(false);
                $table->unsignedSmallInteger('units_count')->default(1);
                $table->string('teeth_numbers', 100)->nullable();
                
                $table->string('status', 30)->default('in_progress')->index(); // 'in_progress', 'overdue', 'received', 'delivered', 'canceled'
                $table->dateTime('sent_at')->index();
                $table->dateTime('expected_delivery_at')->nullable()->index();
                $table->dateTime('received_at')->nullable()->index();
                
                $table->text('notes')->nullable();
                $table->foreignId('parent_order_id')->nullable()->constrained('booking_laboratory_orders')->nullOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_laboratory_orders');
    }
};
