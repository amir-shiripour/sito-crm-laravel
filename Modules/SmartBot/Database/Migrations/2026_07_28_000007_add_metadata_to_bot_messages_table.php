<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('bot_messages', 'metadata')) {
                $table->json('metadata')->nullable()->after('confidence_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bot_messages', function (Blueprint $table) {
            if (Schema::hasColumn('bot_messages', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
