<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plan_workflow_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->cascadeOnDelete();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
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
        });

        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('binding_id')->nullable()->after('current_node_id');
            $table->string('tooth_context')->nullable()->after('binding_id');
            $table->json('item_context')->nullable()->after('tooth_context');

            $table->foreign('binding_id')->references('id')->on('treatment_plan_workflow_bindings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->dropForeign(['binding_id']);
            $table->dropColumn(['binding_id', 'tooth_context', 'item_context']);
        });

        Schema::dropIfExists('treatment_plan_workflow_bindings');
    }
};
