<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('entity_type'); // e.g. 'treatment_plan'
            $table->json('blocks')->nullable(); // block hierarchy configuration
            $table->longText('body')->nullable(); // fallback HTML body
            $table->text('css_style')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('contract_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('contract_templates')->onDelete('cascade');
            $table->string('name');
            $table->string('entity_type');
            $table->string('trigger_event'); // e.g. 'created', 'status_changed'
            $table->json('trigger_statuses')->nullable(); // e.g. ['confirmed']
            $table->json('conditions')->nullable(); // conditions JSON
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_create')->default(true);
            $table->boolean('prevent_duplicate')->default(true);
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('template_id')->nullable()->constrained('contract_templates')->onDelete('set null');
            $table->foreignId('rule_id')->nullable()->constrained('contract_rules')->onDelete('set null');
            $table->string('contractable_type'); // Polymorphic
            $table->unsignedBigInteger('contractable_id'); // Polymorphic
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('user_id'); // Created/assigned user
            $table->string('title');
            $table->json('blocks_data')->nullable(); // actual rendered block data snapshot
            $table->longText('rendered_body');
            $table->string('status')->default('draft'); // draft, active, signed, cancelled
            $table->timestamp('signed_at')->nullable();
            $table->json('meta')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['contractable_type', 'contractable_id']);
        });

        Schema::create('contract_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_settings');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_rules');
        Schema::dropIfExists('contract_templates');
    }
};
