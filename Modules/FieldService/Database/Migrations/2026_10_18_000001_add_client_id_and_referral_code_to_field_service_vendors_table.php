<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('field_service_vendors')) {
            Schema::table('field_service_vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('field_service_vendors', 'client_id')) {
                    $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->nullOnDelete();
                }
                if (!Schema::hasColumn('field_service_vendors', 'referral_code')) {
                    $table->string('referral_code', 40)->nullable()->unique()->after('license_number');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('field_service_vendors')) {
            Schema::table('field_service_vendors', function (Blueprint $table) {
                if (Schema::hasColumn('field_service_vendors', 'client_id')) {
                    $table->dropForeign(['client_id']);
                    $table->dropColumn('client_id');
                }
                if (Schema::hasColumn('field_service_vendors', 'referral_code')) {
                    $table->dropColumn('referral_code');
                }
            });
        }
    }
};
