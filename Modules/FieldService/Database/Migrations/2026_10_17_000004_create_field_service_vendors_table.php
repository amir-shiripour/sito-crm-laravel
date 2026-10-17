<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_vendors')) {
            Schema::create('field_service_vendors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('store_name', 120);
                $table->string('owner_name', 100);
                $table->string('mobile', 20)->unique()->index();
                $table->string('phone', 30)->nullable();
                $table->string('license_number', 50)->nullable();
                $table->string('province', 60)->default('مازندران');
                $table->string('city', 60)->default('بابل');
                $table->text('address')->nullable();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->decimal('referral_commission_percent', 5, 2)->default(5.00); // 5% پورسانت معرف
                $table->string('status', 30)->default('active'); // active, pending, suspended
                $table->text('notes')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_vendors');
    }
};
