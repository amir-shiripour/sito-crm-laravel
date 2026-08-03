<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    private function convertToGregorian($dateStr) {
        if (empty($dateStr)) return null;
        
        $dateStr = substr((string)$dateStr, 0, 10);
        $dateNorm = str_replace('/', '-', $dateStr);
        if (str_starts_with($dateNorm, '13') || str_starts_with($dateNorm, '14')) {
            try {
                return \Morilog\Jalali\Jalalian::fromFormat('Y-m-d', $dateNorm)->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                // If it fails (maybe already gregorian somehow or invalid), fallback
                try {
                    return Carbon::parse($dateNorm)->format('Y-m-d');
                } catch (\Exception $e2) {
                    return null;
                }
            }
        }
        
        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Convert Data for service_invoices
        $invoices = DB::table('service_invoices')->get();
        foreach ($invoices as $invoice) {
            DB::table('service_invoices')->where('id', $invoice->id)->update([
                'issue_date' => $this->convertToGregorian($invoice->issue_date),
                'due_date' => $this->convertToGregorian($invoice->due_date)
            ]);
        }

        // 1. Convert Data for service_orders
        $orders = DB::table('service_orders')->get();
        foreach ($orders as $order) {
            DB::table('service_orders')->where('id', $order->id)->update([
                'issue_date' => $this->convertToGregorian($order->issue_date),
                'renewal_date' => $this->convertToGregorian($order->renewal_date)
            ]);
        }

        // 2. Change column types to DATE
        Schema::table('service_invoices', function (Blueprint $table) {
            $table->date('issue_date')->nullable()->change();
            $table->date('due_date')->nullable()->change();
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->date('issue_date')->nullable()->change();
            $table->date('renewal_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Can't automatically convert back accurately without timezone assumptions,
        // but we'll just switch the column type back to string.
        Schema::table('service_invoices', function (Blueprint $table) {
            $table->string('issue_date')->nullable()->change();
            $table->string('due_date')->nullable()->change();
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('issue_date')->nullable()->change();
            $table->string('renewal_date')->nullable()->change();
        });
    }
};
