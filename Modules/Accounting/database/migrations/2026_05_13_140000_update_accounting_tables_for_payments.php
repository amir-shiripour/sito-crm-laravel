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
        // 1. Add fields to accounting_documents table
        Schema::table('accounting_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_documents', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('description');
            }
            if (!Schema::hasColumn('accounting_documents', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('accounting_documents', 'attachment')) {
                $table->string('attachment')->nullable()->after('reference_number');
            }
        });

        // 2. Add invoice_id to accounting_cheques table
        Schema::table('cheques', function (Blueprint $table) {
            if (!Schema::hasColumn('cheques', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_documents', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_documents', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('accounting_documents', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
            if (Schema::hasColumn('accounting_documents', 'attachment')) {
                $table->dropColumn('attachment');
            }
        });

        Schema::table('cheques', function (Blueprint $table) {
            if (Schema::hasColumn('cheques', 'invoice_id')) {
                $table->dropColumn('invoice_id');
            }
        });
    }
};
