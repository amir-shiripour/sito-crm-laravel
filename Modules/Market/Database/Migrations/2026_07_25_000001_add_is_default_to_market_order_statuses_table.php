<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('market_order_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('market_order_statuses', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_active');
            }
        });

        // Set default status on existing record
        $firstStatus = DB::table('market_order_statuses')
            ->where('client_label', 'ثبت اولیه سفارش')
            ->orWhere('system_type', 'pending')
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($firstStatus) {
            DB::table('market_order_statuses')
                ->where('id', $firstStatus->id)
                ->update(['is_default' => true]);
        } else {
            // Insert a default status if table is empty
            DB::table('market_order_statuses')->insert([
                'admin_label' => 'در انتظار بررسی مدیریت',
                'client_label' => 'ثبت اولیه سفارش',
                'color_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'system_type' => 'pending',
                'sort_order' => 10,
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_order_statuses', function (Blueprint $table) {
            if (Schema::hasColumn('market_order_statuses', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};
