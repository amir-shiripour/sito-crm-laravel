<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                // Cure — Basic
                if (!Schema::hasColumn('booking_settings', 'cure_default_status')) {
                    $table->string('cure_default_status', 20)->default('draft');
                }
                if (!Schema::hasColumn('booking_settings', 'cure_allow_edit_confirmed')) {
                    $table->boolean('cure_allow_edit_confirmed')->default(false);
                }

                // Cure — Discount & Financial
                if (!Schema::hasColumn('booking_settings', 'cure_allow_discount')) {
                    $table->boolean('cure_allow_discount')->default(true);
                }
                if (!Schema::hasColumn('booking_settings', 'cure_max_discount_percent')) {
                    $table->unsignedInteger('cure_max_discount_percent')->default(100);
                }
                if (!Schema::hasColumn('booking_settings', 'cure_discount_type')) {
                    $table->string('cure_discount_type', 10)->default('amount');
                }
                if (!Schema::hasColumn('booking_settings', 'cure_auto_tax')) {
                    $table->boolean('cure_auto_tax')->default(false);
                }

                // Cure — Warranty
                if (!Schema::hasColumn('booking_settings', 'cure_warranty_enabled')) {
                    $table->boolean('cure_warranty_enabled')->default(false);
                }
                if (!Schema::hasColumn('booking_settings', 'cure_default_warranty_months')) {
                    $table->unsignedInteger('cure_default_warranty_months')->default(6);
                }
                if (!Schema::hasColumn('booking_settings', 'cure_default_warranty_text')) {
                    $table->string('cure_default_warranty_text')->nullable();
                }

                // Cure — Notes
                if (!Schema::hasColumn('booking_settings', 'cure_default_notes')) {
                    $table->text('cure_default_notes')->nullable();
                }
                if (!Schema::hasColumn('booking_settings', 'cure_require_notes')) {
                    $table->boolean('cure_require_notes')->default(false);
                }

                // Cure — Dental Chart
                if (!Schema::hasColumn('booking_settings', 'cure_tooth_numbering_system')) {
                    $table->string('cure_tooth_numbering_system', 20)->default('universal');
                }
                if (!Schema::hasColumn('booking_settings', 'cure_auto_highlight_teeth')) {
                    $table->boolean('cure_auto_highlight_teeth')->default(true);
                }
                if (!Schema::hasColumn('booking_settings', 'cure_show_tooth_filter')) {
                    $table->boolean('cure_show_tooth_filter')->default(true);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                $cols = [
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
                ];
                $toDrop = array_filter($cols, fn($c) => Schema::hasColumn('booking_settings', $c));
                if (!empty($toDrop)) {
                    $table->dropColumn(array_values($toDrop));
                }
            });
        }
    }
};
