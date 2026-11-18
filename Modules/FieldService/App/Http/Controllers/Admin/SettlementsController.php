<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\FieldService\App\Enums\SettlementStatus;
use Modules\FieldService\App\Models\PayaSettlement;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\PayaBatchExporterService;
use Modules\FieldService\App\Support\TechnicianCustomFieldsHelper;

class SettlementsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if (TechnicianCustomFieldsHelper::isTechnicianOnly($user)) {
            $technician = Technician::where('user_id', $user->id)->first();
            $techId = $technician?->id ?? 0;

            $settlements = PayaSettlement::where('technician_id', $techId)
                ->latest()
                ->paginate(15);

            $pendingTotal = PayaSettlement::where('technician_id', $techId)
                ->where('status', SettlementStatus::PENDING)
                ->sum('amount');

            $completedTotal = PayaSettlement::where('technician_id', $techId)
                ->where('status', SettlementStatus::COMPLETED)
                ->sum('amount');

            return view('fieldservice::technician.crm-settlements', compact('settlements', 'pendingTotal', 'completedTotal', 'technician'));
        }

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
