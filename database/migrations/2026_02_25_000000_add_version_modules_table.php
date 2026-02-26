<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * اجرای مایگریشن برای اضافه کردن ستون نسخه به جدول ماژول‌ها
     */
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'version')) {
                $table->string('version')->nullable()->after('description');
            }
        });
    }

    /**
     * بازگشت مایگریشن
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};
