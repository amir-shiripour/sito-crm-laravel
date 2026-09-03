<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('booking_services')) {
            return;
        }

        Schema::table('booking_services', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_services', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('status')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('booking_services')) {
            return;
        }

        Schema::table('booking_services', function (Blueprint $table) {
            if (Schema::hasColumn('booking_services', 'sort_order')) {
                $table->dropIndex(['sort_order']);
                $table->dropColumn('sort_order');
            }
        });
    }
};
