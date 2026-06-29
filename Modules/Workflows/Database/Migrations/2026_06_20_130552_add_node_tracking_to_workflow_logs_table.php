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
        Schema::table('workflow_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('from_node_id')->nullable()->after('stage_id');
            $table->unsignedBigInteger('to_node_id')->nullable()->after('from_node_id');
            $table->string('transition_type')->nullable()->after('to_node_id'); // ADVANCE, BACK, START, RESTART, CANCEL

            $table->foreign('from_node_id')->references('id')->on('workflow_nodes')->nullOnDelete();
            $table->foreign('to_node_id')->references('id')->on('workflow_nodes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_logs', function (Blueprint $table) {
            $table->dropForeign(['from_node_id']);
            $table->dropForeign(['to_node_id']);
            $table->dropColumn(['from_node_id', 'to_node_id', 'transition_type']);
        });
    }
};
