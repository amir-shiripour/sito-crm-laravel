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
        Schema::table('bot_answers', function (Blueprint $table) {
            $table->json('smart_attachments')->nullable()->after('show_add_to_cart');
        });

        Schema::table('bot_menu_items', function (Blueprint $table) {
            $table->json('smart_attachments')->nullable()->after('response_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bot_answers', function (Blueprint $table) {
            $table->dropColumn('smart_attachments');
        });

        Schema::table('bot_menu_items', function (Blueprint $table) {
            $table->dropColumn('smart_attachments');
        });
    }
};
