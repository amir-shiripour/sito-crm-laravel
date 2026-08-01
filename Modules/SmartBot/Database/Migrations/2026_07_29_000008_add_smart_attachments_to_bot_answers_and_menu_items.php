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
        if (!Schema::hasColumn('bot_answers', 'smart_attachments')) {
            Schema::table('bot_answers', function (Blueprint $table) {
                $table->json('smart_attachments')->nullable()->after('is_default');
            });
        }

        if (!Schema::hasColumn('bot_menu_items', 'smart_attachments')) {
            Schema::table('bot_menu_items', function (Blueprint $table) {
                $table->json('smart_attachments')->nullable()->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bot_answers', 'smart_attachments')) {
            Schema::table('bot_answers', function (Blueprint $table) {
                $table->dropColumn('smart_attachments');
            });
        }

        if (Schema::hasColumn('bot_menu_items', 'smart_attachments')) {
            Schema::table('bot_menu_items', function (Blueprint $table) {
                $table->dropColumn('smart_attachments');
            });
        }
    }
};
