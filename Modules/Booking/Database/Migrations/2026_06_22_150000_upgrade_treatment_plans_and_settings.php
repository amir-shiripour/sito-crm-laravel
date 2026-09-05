<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Change status column type in treatment_plans to string and add assigned_users
        if (Schema::hasTable('treatment_plans')) {
            Schema::table('treatment_plans', function (Blueprint $table) {
                if (Schema::hasColumn('treatment_plans', 'status')) {
                    $table->string('status', 50)->default('draft')->change();
                }
                if (!Schema::hasColumn('treatment_plans', 'assigned_users')) {
                    $col = $table->json('assigned_users')->nullable();
                    if (Schema::hasColumn('treatment_plans', 'items')) {
                        $col->after('items');
                    }
                }
            });
        }

        // 3. Create treatment_plan_snapshots table
        if (!Schema::hasTable('treatment_plan_snapshots')) {
            Schema::create('treatment_plan_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->cascadeOnDelete();
                $table->string('status_from')->nullable();
                $table->string('status_to');
                $table->json('data');
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Add cure_statuses and cure_assignable_roles to booking_settings
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_settings', 'cure_statuses')) {
                    $table->json('cure_statuses')->nullable();
                }
                if (!Schema::hasColumn('booking_settings', 'cure_assignable_roles')) {
                    $table->json('cure_assignable_roles')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('booking_settings', 'cure_statuses')) {
                    $cols[] = 'cure_statuses';
                }
                if (Schema::hasColumn('booking_settings', 'cure_assignable_roles')) {
                    $cols[] = 'cure_assignable_roles';
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('treatment_plan_snapshots');

        if (Schema::hasTable('treatment_plans')) {
            Schema::table('treatment_plans', function (Blueprint $table) {
                if (Schema::hasColumn('treatment_plans', 'assigned_users')) {
                    $table->dropColumn('assigned_users');
                }
                if (Schema::hasColumn('treatment_plans', 'status')) {
                    $table->enum('status', ['draft', 'confirmed'])->default('draft')->change();
                }
            });
        }
    }
};
