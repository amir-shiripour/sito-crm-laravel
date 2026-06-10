<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            // Cure — Basic
            $table->string('cure_default_status', 20)->default('draft');
            $table->boolean('cure_allow_edit_confirmed')->default(false);

            // Cure — Discount & Financial
            $table->boolean('cure_allow_discount')->default(true);
            $table->unsignedInteger('cure_max_discount_percent')->default(100);
            $table->string('cure_discount_type', 10)->default('amount');
            $table->boolean('cure_auto_tax')->default(false);

            // Cure — Warranty
            $table->boolean('cure_warranty_enabled')->default(false);
            $table->unsignedInteger('cure_default_warranty_months')->default(6);
            $table->string('cure_default_warranty_text')->nullable();

            // Cure — Notes
            $table->text('cure_default_notes')->nullable();
            $table->boolean('cure_require_notes')->default(false);

            // Cure — Dental Chart
            $table->string('cure_tooth_numbering_system', 20)->default('universal');
            $table->boolean('cure_auto_highlight_teeth')->default(true);
            $table->boolean('cure_show_tooth_filter')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->dropColumn([
                'cure_default_status',
                'cure_allow_edit_confirmed',
                'cure_allow_discount',
                'cure_max_discount_percent',
                'cure_discount_type',
                'cure_auto_tax',
                'cure_warranty_enabled',
                'cure_default_warranty_months',
                'cure_default_warranty_text',
                'cure_default_notes',
                'cure_require_notes',
                'cure_tooth_numbering_system',
                'cure_auto_highlight_teeth',
                'cure_show_tooth_filter',
            ]);
        });
    }
};
