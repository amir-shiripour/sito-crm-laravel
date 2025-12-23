<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'entry_at_utc')) {
                $table->timestamp('entry_at_utc')->nullable()->after('end_at_utc');
            }
            if (!Schema::hasColumn('appointments', 'exit_at_utc')) {
                $table->timestamp('exit_at_utc')->nullable()->after('entry_at_utc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'exit_at_utc')) {
                $table->dropColumn('exit_at_utc');
            }
            if (Schema::hasColumn('appointments', 'entry_at_utc')) {
                $table->dropColumn('entry_at_utc');
            }
        });
    }
};
