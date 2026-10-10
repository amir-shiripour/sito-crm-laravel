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
        // 1. If projects_messages table does not exist at all, create it completely
        if (!Schema::hasTable('projects_messages')) {
            Schema::create('projects_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->text('body');
                $table->json('attachments')->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->timestamp('pinned_at')->nullable();
                $table->unsignedBigInteger('pinned_by')->nullable();
                $table->timestamps();

                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                    $table->foreign('pinned_by')->references('id')->on('users')->nullOnDelete();
                }
                $table->foreign('parent_id')->references('id')->on('projects_messages')->nullOnDelete();
            });
            return;
        }

        // 2. If table exists, ensure parent_id column and foreign key are added safely
        Schema::table('projects_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('projects_messages', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('project_id');
                $table->foreign('parent_id')->references('id')->on('projects_messages')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('projects_messages') && Schema::hasColumn('projects_messages', 'parent_id')) {
            Schema::table('projects_messages', function (Blueprint $table) {
                try {
                    $table->dropForeign(['parent_id']);
                } catch (\Throwable) {}
                $table->dropColumn('parent_id');
            });
        }
    }
};
