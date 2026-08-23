<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('projects_checklist_comments')) {
            Schema::create('projects_checklist_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('checklist_item_id')
                    ->constrained('projects_checklist_items')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('body');
                $table->timestamps();

                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                }

                $table->index(['checklist_item_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_checklist_comments');
    }
};
