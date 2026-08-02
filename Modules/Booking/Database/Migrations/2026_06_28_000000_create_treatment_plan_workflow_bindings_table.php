<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('treatment_plan_workflow_bindings')) {
            Schema::create('treatment_plan_workflow_bindings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->cascadeOnDelete();
                $table->unsignedBigInteger('workflow_id');
                $table->enum('scope', ['plan', 'item', 'tooth'])->default('plan');
                $table->string('item_key')->nullable(); // holds unique uuid of item
                $table->string('tooth')->nullable(); // holds tooth number or 'all'
                $table->json('trigger_statuses')->nullable();
                $table->string('previous_status')->nullable();
                $table->decimal('min_amount', 14, 2)->nullable();
                $table->boolean('auto_trigger')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['treatment_plan_id', 'scope']);
                $table->index(['treatment_plan_id', 'tooth']);

                if (Schema::hasTable('workflows')) {
                    $table->foreign('workflow_id')->references('id')->on('workflows')->cascadeOnDelete();
                }
            });
        }

        if (Schema::hasTable('workflow_instances')) {
            Schema::table('workflow_instances', function (Blueprint $table) {
                if (!Schema::hasColumn('workflow_instances', 'binding_id')) {
                    $table->unsignedBigInteger('binding_id')->nullable()->after('current_node_id');
                    $table->string('tooth_context')->nullable()->after('binding_id');
                    $table->json('item_context')->nullable()->after('tooth_context');

                    $table->foreign('binding_id')->references('id')->on('treatment_plan_workflow_bindings')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_instances')) {
            Schema::table('workflow_instances', function (Blueprint $table) {
                if (Schema::hasColumn('workflow_instances', 'binding_id')) {
                    try {
                        $table->dropForeign(['binding_id']);
                    } catch (\Throwable $e) {
                        // Ignored if FK doesn't exist
                    }
                    $table->dropColumn(['binding_id', 'tooth_context', 'item_context']);
                }
            });
        }

        Schema::dropIfExists('treatment_plan_workflow_bindings');
    }
};
