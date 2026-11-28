<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_laboratory_orders')) {
            Schema::table('booking_laboratory_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_laboratory_orders', 'variant_key')) {
                    $table->string('variant_key', 80)->nullable()->after('category_type')->index();
                }
                if (!Schema::hasColumn('booking_laboratory_orders', 'variant_title')) {
                    $table->string('variant_title', 150)->nullable()->after('variant_key');
                }
                if (!Schema::hasColumn('booking_laboratory_orders', 'active_branch_label')) {
                    $table->string('active_branch_label', 200)->nullable()->after('variant_title');
                }
            });
        }

        if (Schema::hasTable('booking_laboratory_stages')) {
            Schema::table('booking_laboratory_stages', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_laboratory_stages', 'parent_stage_id')) {
                    $table->foreignId('parent_stage_id')
                        ->nullable()
                        ->after('order_id')
                        ->constrained('booking_laboratory_stages')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'layer_depth')) {
                    $table->unsignedSmallInteger('layer_depth')->default(0)->after('parent_stage_id');
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'revision_round')) {
                    $table->unsignedSmallInteger('revision_round')->default(0)->after('layer_depth');
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'branch_key')) {
                    $table->string('branch_key', 80)->nullable()->after('revision_round');
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'branch_label')) {
                    $table->string('branch_label', 150)->nullable()->after('branch_key');
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'stage_type')) {
                    $table->string('stage_type', 30)->default('standard')->after('stage_title')->index();
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'config_snapshot')) {
                    $table->json('config_snapshot')->nullable()->after('is_receive_stage');
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'selected_option_key')) {
                    $table->string('selected_option_key', 80)->nullable()->after('config_snapshot');
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'selected_option_label')) {
                    $table->string('selected_option_label', 150)->nullable()->after('selected_option_key');
                }
                if (!Schema::hasColumn('booking_laboratory_stages', 'selected_meta')) {
                    $table->json('selected_meta')->nullable()->after('selected_option_label');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_laboratory_stages')) {
            Schema::table('booking_laboratory_stages', function (Blueprint $table) {
                if (Schema::hasColumn('booking_laboratory_stages', 'parent_stage_id')) {
                    $table->dropConstrainedForeignId('parent_stage_id');
                }
                $columns = [
                    'layer_depth',
                    'revision_round',
                    'branch_key',
                    'branch_label',
                    'stage_type',
                    'config_snapshot',
                    'selected_option_key',
                    'selected_option_label',
                    'selected_meta',
                ];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('booking_laboratory_stages', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('booking_laboratory_orders')) {
            Schema::table('booking_laboratory_orders', function (Blueprint $table) {
                $columns = ['variant_key', 'variant_title', 'active_branch_label'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('booking_laboratory_orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
