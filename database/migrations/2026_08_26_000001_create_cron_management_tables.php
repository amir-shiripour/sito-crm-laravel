<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cron_tasks')) {
            Schema::create('cron_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('module', 64)->nullable()->default('Core')->index();
                $table->string('command')->index();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('expression', 64)->default('everyFiveMinutes');
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('prevent_overlap')->default(true);
                $table->boolean('run_in_background')->default(false);
                $table->timestamp('last_run_at')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->string('last_status', 32)->nullable()->index(); // success, failed, running
                $table->unsignedInteger('last_duration_ms')->nullable();
                $table->text('last_error_message')->nullable();
                $table->boolean('is_system')->default(false);
                $table->json('parameters')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cron_task_logs')) {
            Schema::create('cron_task_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cron_task_id')->constrained('cron_tasks')->cascadeOnDelete();
                $table->string('status', 32)->default('running')->index(); // success, failed, running
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->longText('output')->nullable();
                $table->text('error_message')->nullable();
                $table->string('triggered_by', 64)->default('system_cron'); // system_cron, manual
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_task_logs');
        Schema::dropIfExists('cron_tasks');
    }
};
