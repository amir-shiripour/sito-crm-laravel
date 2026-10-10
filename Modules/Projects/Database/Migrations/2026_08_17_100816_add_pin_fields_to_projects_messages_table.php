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
        if (!Schema::hasTable('projects_messages')) {
            Schema::create('projects_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
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
            });
            return;
        }

        Schema::table('projects_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('projects_messages', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('attachments');
            }
            if (!Schema::hasColumn('projects_messages', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            }
            if (!Schema::hasColumn('projects_messages', 'pinned_by')) {
                $table->unsignedBigInteger('pinned_by')->nullable()->after('pinned_at');
                if (Schema::hasTable('users')) {
                    $table->foreign('pinned_by')->references('id')->on('users')->nullOnDelete();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('projects_messages')) {
            Schema::table('projects_messages', function (Blueprint $table) {
                if (Schema::hasColumn('projects_messages', 'pinned_by')) {
                    if (Schema::hasTable('users')) {
                        try {
                            $table->dropForeign(['pinned_by']);
                        } catch (\Throwable) {}
                    }
                }
                $colsToDrop = array_filter(['is_pinned', 'pinned_at', 'pinned_by'], fn($c) => Schema::hasColumn('projects_messages', $c));
                if (!empty($colsToDrop)) {
                    $table->dropColumn($colsToDrop);
                }
            });
        }
    }
};
