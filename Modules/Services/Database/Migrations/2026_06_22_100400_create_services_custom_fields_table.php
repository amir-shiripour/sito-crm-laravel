<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->morphs('fieldable');
            $table->string('label');
            $table->string('key');
            $table->enum('type', [
                'text', 'textarea', 'number', 'date', 'datetime',
                'select', 'multiselect', 'checkbox', 'radio',
                'email', 'url', 'phone', 'file'
            ])->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);

            // Pricing
            $table->boolean('has_pricing')->default(false);
            $table->enum('pricing_type', ['fixed', 'percentage'])->nullable();
            $table->unsignedBigInteger('pricing_amount')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('services_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained('services_custom_fields')->cascadeOnDelete();
            $table->morphs('valueable');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services_custom_field_values');
        Schema::dropIfExists('services_custom_fields');
    }
};
