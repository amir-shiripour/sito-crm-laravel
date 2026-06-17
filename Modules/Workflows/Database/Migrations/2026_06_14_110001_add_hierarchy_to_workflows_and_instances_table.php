<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('is_active');
            $table->foreign('parent_id')->references('id')->on('workflows')->onDelete('set null');
        });

        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_instance_id')->nullable()->after('workflow_id');
            $table->unsignedBigInteger('current_node_id')->nullable()->after('current_stage_id');

            $table->foreign('parent_instance_id')->references('id')->on('workflow_instances')->onDelete('set null');
            $table->foreign('current_node_id')->references('id')->on('workflow_nodes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->dropForeign(['current_node_id']);
            $table->dropForeign(['parent_instance_id']);
            $table->dropColumn(['current_node_id', 'parent_instance_id']);
        });

        Schema::table('workflows', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id']);
        });
    }
};
