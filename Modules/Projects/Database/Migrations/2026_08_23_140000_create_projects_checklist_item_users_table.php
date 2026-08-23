<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('projects_checklist_item_users')) {
            Schema::create('projects_checklist_item_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('checklist_item_id')->constrained('projects_checklist_items')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('is_done')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['checklist_item_id', 'user_id'], 'chk_item_user_unique');
            });
        }

        // Migrate existing assigned_to data to the new pivot table
        if (Schema::hasTable('projects_checklist_items') && Schema::hasColumn('projects_checklist_items', 'assigned_to')) {
            $existingItems = DB::table('projects_checklist_items')
                ->whereNotNull('assigned_to')
                ->select('id', 'assigned_to', 'is_done', 'completed_at', 'created_at', 'updated_at')
                ->get();

            foreach ($existingItems as $item) {
                if ($item->assigned_to) {
                    $exists = DB::table('projects_checklist_item_users')
                        ->where('checklist_item_id', $item->id)
                        ->where('user_id', $item->assigned_to)
                        ->exists();

                    if (!$exists) {
                        DB::table('projects_checklist_item_users')->insert([
                            'checklist_item_id' => $item->id,
                            'user_id' => $item->assigned_to,
                            'is_done' => (bool)$item->is_done,
                            'completed_at' => $item->completed_at,
                            'created_at' => $item->created_at ?? now(),
                            'updated_at' => $item->updated_at ?? now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_checklist_item_users');
    }
};
