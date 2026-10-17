<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\FieldService\App\Models\PayaSettlement;
use Modules\FieldService\App\Services\PayaBatchExporterService;

class SettlementsController extends Controller
{
    public function index(): View
    {
        return view('fieldservice::admin.settlements-index');
    }

    public function exportBatch(PayaBatchExporterService $exporter): Response
    {
        $pending = PayaSettlement::where('status', 'pending')
            ->with('technician')
            ->get();

        if ($pending->isEmpty()) {
            return response()->make('هیچ رکوردی برای تسویه پایا یافت نشد.', 404);
        }

        $batchRef = 'PAYA-BATCH-' . date('YmdHis');
        $csv = $exporter->generatePayaCsv($pending, $batchRef);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"paya-settlement-{$batchRef}.csv\"",
        ]);
    }
}
