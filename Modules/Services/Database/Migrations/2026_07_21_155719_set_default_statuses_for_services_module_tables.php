<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Services\App\Http\Models\Status;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'services' => 'service',
            'service_orders' => 'order',
            'service_invoices' => 'invoice',
            'service_invoice_payments' => 'payment',
            'services_projects' => 'project',
        ];

        foreach ($tables as $table => $type) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table) || !\Illuminate\Support\Facades\Schema::hasColumn($table, 'status_id')) {
                continue;
            }

            // Find the default status for this type
            $defaultStatus = Status::where('type', $type)->where('is_default', true)->first();

            // If no default status is found, try to find any status for this type
            if (!$defaultStatus) {
                $defaultStatus = Status::where('type', $type)->first();
            }

            if ($defaultStatus) {
                DB::table($table)->whereNull('status_id')->update([
                    'status_id' => $defaultStatus->id
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for data updates
    }
};
