<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('booking_waitlists')) {
            Schema::create('booking_waitlists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
                $table->foreignId('provider_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->date('preferred_date')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedInteger('position')->default(1);
                $table->string('status', 30)->default('waiting');
                $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('notified_at')->nullable();
                $table->timestamp('converted_at')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index(['client_id', 'status']);
                $table->index(['service_id', 'status']);
                $table->index(['provider_user_id', 'status']);
                $table->index(['status', 'position']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_waitlists');
    }
};
