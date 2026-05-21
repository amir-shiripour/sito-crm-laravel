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
        Schema::table('accounting_banks', function (Blueprint $table) {
            // اضافه کردن ستون موجودی (balance) از نوع decimal (چون در مدل هم decimal تعریف شده است)
            // مقدار پیش‌فرض آن 0 قرار داده می‌شود تا خطایی رخ ندهد
            if (!Schema::hasColumn('accounting_banks', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0)->after('iban');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_banks', function (Blueprint $table) {
            // حذف ستون در صورت نیاز به بازگشت (rollback)
            if (Schema::hasColumn('accounting_banks', 'balance')) {
                $table->dropColumn('balance');
            }
        });
    }
};
