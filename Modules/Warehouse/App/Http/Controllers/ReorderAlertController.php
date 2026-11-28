<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Modules\Warehouse\App\Services\WarehousePdfService;
use Modules\Warehouse\App\Services\WarehouseValuationService;
use Modules\Warehouse\Entities\WarehouseCategory;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseSetting;

class ReorderAlertController extends Controller
{
    protected WarehouseValuationService $valuationService;

    public function __construct(WarehouseValuationService $valuationService)
    {
        $this->valuationService = $valuationService;
    }

    /**
     * Display dual-level (clinic & central warehouse) reorder point and expiry alerts.
     */
    public function index(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی در تنظیمات غیرفعال شده است.');
        }

        $search = trim((string)$request->input('search', ''));
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');
        $scope = (string)$request->input('scope', 'alerts_only');

        $report = $this->valuationService->getReorderAlertsReport([
            'search' => $search,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'scope' => $scope,
        ]);

        $allRows = $report['rows'];
        $perPage = 25;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedItems = $allRows->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $rows = (new LengthAwarePaginator(
            $pagedItems,
            $allRows->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        ))->withQueryString();

        $mainWarehouse = $report['main_warehouse'];
        $summary = $report['summary'];
        $categories = WarehouseCategory::roots()->active()->with('children')->orderBy('order')->orderBy('name')->get();

        return view('warehouse::reorder_alerts.index', compact(
            'rows',
            'mainWarehouse',
            'summary',
            'categories',
            'search',
            'categoryId',
            'subcategoryId',
            'scope'
        ));
    }

    /**
     * Update item min_stock and reorder_point inline from the reorder alerts table.
     */
    public function updateThresholds(Request $request, WarehouseItem $item)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'بخش انبار موجودی غیرفعال است.',
            ], 403);
        }

        $validated = $request->validate([
            'min_stock' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'reorder_point' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);

        $item->update([
            'min_stock' => (float)$validated['min_stock'],
            'reorder_point' => (float)$validated['reorder_point'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'آستانه‌های هشدار با موفقیت به‌روزرسانی شد.',
            'min_stock' => (float)$item->min_stock,
            'reorder_point' => (float)$item->reorder_point,
        ]);
    }

    /**
     * Export filtered reorder alerts list as official Core PDF (Browsershot).
     */
    public function exportPdf(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            abort(403, 'بخش انبار موجودی غیرفعال شده است.');
        }

        $search = trim((string)$request->input('search', ''));
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');
        $scope = (string)$request->input('scope', 'alerts_only');

        $report = $this->valuationService->getReorderAlertsReport([
            'search' => $search,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'scope' => $scope,
        ]);

        $rows = $report['rows']->take(500);
        $mainWarehouse = $report['main_warehouse'];
        $summary = $report['summary'];

        $filename = 'reorder-alerts-' . $scope . '-' . date('Y-m-d') . '.pdf';

        return app(WarehousePdfService::class)->downloadInline(
            'warehouse::pdf.reorder_alerts',
            compact('rows', 'mainWarehouse', 'summary', 'scope', 'search'),
            $filename,
            ['landscape' => true]
        );
    }
}
