<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // اگر این ستون‌ها هستن، دوباره اضافه نکن
            if (!Schema::hasColumn('clients', 'password')) {
                $table->string('password')->nullable()->after('username');
            }

            if (!Schema::hasColumn('clients', 'remember_token')) {
                $table->rememberToken();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('clients', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }
};
