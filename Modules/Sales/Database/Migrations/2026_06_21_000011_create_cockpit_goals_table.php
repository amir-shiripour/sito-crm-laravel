<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cockpit_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('goal_type', [
                'daily_calls',
                'daily_answered',
                'weekly_followups',
                'monthly_clients',
                'conversion_rate',
                'talk_time_minutes'
            ]);
            $table->integer('target_value');
            $table->enum('period', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->date('active_from')->nullable();
            $table->date('active_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cockpit_goals');
    }
};
