<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_paya_settlements')) {
            Schema::create('field_service_paya_settlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('technician_id')->constrained('field_service_technicians')->cascadeOnDelete();
                $table->string('tracking_code', 50)->unique();
                $table->decimal('amount', 14, 2);
                $table->string('shaba_number', 40);
                $table->string('account_owner_name', 100);
                $table->string('status', 30)->default('pending'); // SettlementStatus enum: pending, processing, completed, rejected
                $table->string('batch_reference', 100)->nullable(); // کد پیگیری بچ پایا بانک مرکزی
                $table->timestamp('requested_at')->useCurrent();
                $table->timestamp('processed_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_paya_settlements');
    }
};
