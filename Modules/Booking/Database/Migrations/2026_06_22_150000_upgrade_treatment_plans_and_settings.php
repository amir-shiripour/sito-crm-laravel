<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Change status column type in treatment_plans to string
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->string('status', 50)->default('draft')->change();
        });

        // 2. Add assigned_users column to treatment_plans
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->json('assigned_users')->nullable()->after('items');
        });

        // 3. Create treatment_plan_snapshots table
        Schema::create('treatment_plan_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->cascadeOnDelete();
            $table->string('status_from')->nullable();
            $table->string('status_to');
            $table->json('data');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Add cure_statuses and cure_assignable_roles to booking_settings
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->json('cure_statuses')->nullable();
            $table->json('cure_assignable_roles')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->dropColumn(['cure_statuses', 'cure_assignable_roles']);
        });

        Schema::dropIfExists('treatment_plan_snapshots');

        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dropColumn('assigned_users');
        });

        // Reverting string to enum
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->enum('status', ['draft', 'confirmed'])->default('draft')->change();
        });
    }
};
