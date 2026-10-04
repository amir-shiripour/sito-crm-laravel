<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('property_revisions')) {
            Schema::create('property_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type', 50)->default('property_update'); // initial_creation, property_update, rental_config_update
                $table->string('status', 30)->default('pending'); // pending, approved, rejected
                $table->json('old_data')->nullable();
                $table->json('new_data')->nullable();
                $table->json('changes_summary')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['property_id', 'status']);
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('property_revisions');
    }
};
