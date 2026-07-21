<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();

            // Foreign keys as plain columns first
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();

            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Pricing
            $table->unsignedBigInteger('base_price')->default(0);
            $table->unsignedBigInteger('setup_fee')->default(0);
            $table->unsignedBigInteger('renewal_price')->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);

            // Billing
            $table->enum('billing_type', ['free', 'one_time', 'recurring'])->default('one_time');
            $table->enum('recurring_period', ['monthly', 'quarterly', 'semi_annual', 'annual', 'custom'])->nullable();
            $table->unsignedInteger('custom_period_days')->nullable();
            $table->unsignedInteger('renewal_reminder_days')->default(7);
            $table->boolean('auto_renewal')->default(false);

            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Add foreign keys after dependent tables are created
        Schema::table('services', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('service_categories')
                ->nullOnDelete();

            $table->foreign('template_id')
                ->references('id')
                ->on('service_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
