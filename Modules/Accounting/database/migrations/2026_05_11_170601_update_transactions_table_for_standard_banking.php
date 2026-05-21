<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_transactions', 'bank_id')) {
                $table->foreignId('bank_id')->nullable()->after('id')->constrained('accounting_banks')->onDelete('set null');
            }

            if (!Schema::hasColumn('accounting_transactions', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('type');
            }

            // 👈 این بلوک جدید را برای حل ارور فعلی اضافه کردیم
            if (!Schema::hasColumn('accounting_transactions', 'reference_code')) {
                $table->string('reference_code')->nullable()->after('payment_method');
            }

            if (Schema::hasColumn('accounting_transactions', 'from_bank_id')) {
                $table->dropForeign(['from_bank_id']);
                $table->dropColumn('from_bank_id');
            }
            if (Schema::hasColumn('accounting_transactions', 'to_bank_id')) {
                $table->dropForeign(['to_bank_id']);
                $table->dropColumn('to_bank_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_transactions', 'from_bank_id')) {
                $table->foreignId('from_bank_id')->nullable()->constrained('accounting_banks')->onDelete('set null');
            }
            if (!Schema::hasColumn('accounting_transactions', 'to_bank_id')) {
                $table->foreignId('to_bank_id')->nullable()->constrained('accounting_banks')->onDelete('set null');
            }

            if (Schema::hasColumn('accounting_transactions', 'bank_id')) {
                $table->dropForeign(['bank_id']);
                $table->dropColumn('bank_id');
            }
            if (Schema::hasColumn('accounting_transactions', 'payment_method')) {
                $table->dropColumn('payment_method');
            }

            // 👈 حذف در صورت بازگشت
            if (Schema::hasColumn('accounting_transactions', 'reference_code')) {
                $table->dropColumn('reference_code');
            }
        });
    }
};
