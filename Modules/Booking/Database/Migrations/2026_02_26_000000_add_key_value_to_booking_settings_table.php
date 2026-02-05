<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            // Add key/value columns for dynamic settings
            // We make 'key' nullable initially or unique?
            // Since the table currently holds a single row for "main settings" (where key is null),
            // and we want to add new rows for key-value pairs.

            $table->string('key')->nullable()->unique()->after('id');
            $table->text('value')->nullable()->after('key');

            // Make existing columns nullable if they aren't already,
            // because key-value rows won't use them.
            // However, modifying existing columns might be risky/complex depending on DB driver.
            // Instead, we just allow them to have default values or be null in code logic.
            // For now, let's just add the columns.
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->dropColumn(['key', 'value']);
        });
    }
};
