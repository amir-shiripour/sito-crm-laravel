<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (!Schema::hasColumn('properties', 'host_id')) {
                    $table->foreignId('host_id')
                        ->nullable()
                        ->after('owner_id')
                        ->constrained('property_hosts')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('properties', 'approval_status')) {
                    $table->string('approval_status', 30)
                        ->default('approved')
                        ->after('publication_status'); // approved, pending_review, rejected
                }

                if (!Schema::hasColumn('properties', 'rejection_reason')) {
                    $table->text('rejection_reason')
                        ->nullable()
                        ->after('approval_status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'host_id')) {
                    $table->dropForeign(['host_id']);
                    $table->dropColumn('host_id');
                }
                if (Schema::hasColumn('properties', 'approval_status')) {
                    $table->dropColumn('approval_status');
                }
                if (Schema::hasColumn('properties', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
            });
        }
    }
};
