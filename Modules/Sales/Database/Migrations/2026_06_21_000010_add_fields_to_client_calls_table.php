<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('client_calls', function (Blueprint $table) {
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound')->after('status');
            $table->integer('duration_seconds')->nullable()->after('direction');
            $table->string('next_action', 255)->nullable()->after('duration_seconds');
            $table->date('next_action_date')->nullable()->after('next_action');
            $table->string('contact_phone', 50)->nullable()->after('next_action_date');
            $table->text('notes')->nullable()->after('contact_phone');
            $table->unsignedBigInteger('campaign_id')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('client_calls', function (Blueprint $table) {
            $table->dropColumn([
                'direction',
                'duration_seconds',
                'next_action',
                'next_action_date',
                'contact_phone',
                'notes',
                'campaign_id',
            ]);
        });
    }
};
