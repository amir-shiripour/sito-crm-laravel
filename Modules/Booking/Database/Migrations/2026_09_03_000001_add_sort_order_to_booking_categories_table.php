<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('booking_categories')) {
            return;
        }

        Schema::table('booking_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('status')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('booking_categories')) {
            return;
        }

        Schema::table('booking_categories', function (Blueprint $table) {
            if (Schema::hasColumn('booking_categories', 'sort_order')) {
                $table->dropIndex(['sort_order']);
                $table->dropColumn('sort_order');
            }
        });
    }
};
