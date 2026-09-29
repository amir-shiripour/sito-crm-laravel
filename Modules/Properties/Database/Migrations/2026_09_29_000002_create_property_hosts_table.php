<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('property_hosts')) {
            Schema::create('property_hosts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->foreignId('owner_id')->nullable()->constrained('property_owners')->nullOnDelete();
                
                $table->string('display_name'); // نام میزبان / نام مجموعه اقامتی
                $table->string('slug')->unique()->nullable();
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->text('about')->nullable();
                
                // اطلاعات مالی و تسویه
                $table->string('shaba_number', 35)->nullable();
                $table->string('bank_name', 100)->nullable();
                $table->string('account_owner_name')->nullable();
                
                // احراز هویت (KYC)
                $table->string('national_code', 15)->nullable();
                $table->string('national_card_image')->nullable();
                $table->string('kyc_status', 30)->default('not_submitted'); // not_submitted, pending, approved, rejected
                $table->text('kyc_rejection_reason')->nullable();
                
                // وضعیت فعالیت و کارمزد
                $table->string('status', 30)->default('pending'); // pending, active, suspended
                $table->decimal('commission_rate', 5, 2)->nullable(); // درصد کارمزد اختصاصی میزبان
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('property_hosts');
    }
};
