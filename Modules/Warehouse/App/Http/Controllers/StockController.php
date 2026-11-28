<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Warehouse\App\Services\WarehousePdfService;
use Modules\Warehouse\App\Services\WarehouseValuationService;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseCategory;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseSetting;

class StockController extends Controller
{
    protected WarehouseValuationService $valuationService;

    public function __construct(WarehouseValuationService $valuationService)
    {
        $this->valuationService = $valuationService;
    }

    /**
     * Live stock levels across all warehouses or filtered to a specific warehouse / sub-warehouse tab.
     */
    public function index(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی در تنظیمات غیرفعال شده است.');
        }

        $warehouses = Warehouse::inventory()->active()
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $warehouseId = $request->filled('warehouse_id') ? (int)$request->input('warehouse_id') : null;
        $selectedWarehouse = $warehouseId ? $warehouses->firstWhere('id', $warehouseId) : null;
        if ($warehouseId && !$selectedWarehouse) {
            $warehouseId = null;
        }

        $search = trim((string)$request->input('search', ''));
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');
        $stockScope = $request->input('stock_scope', 'in_stock'); // in_stock (default), all, zero, expiring

        $query = $this->buildFilteredQuery($warehouseId, $search, $categoryId, $subcategoryId, $stockScope);
        $items = $query->paginate(20)->withQueryString();

        $warehouseSummaries = $this->valuationService->getWarehousesStockSummary($warehouses->pluck('id'));
        $metrics = $this->valuationService->getWarehouseSnapshotMetrics($warehouseId);
        $allWarehousesTotalUnits = collect($warehouseSummaries)->sum('total_units');

        $categories = WarehouseCategory::roots()->active()->with('children')->orderBy('order')->orderBy('name')->get();

        return view('warehouse::stock_levels.index', compact(
            'warehouses',
            'selectedWarehouse',
            'warehouseId',
            'warehouseSummaries',
            'allWarehousesTotalUnits',
            'metrics',
            'items',
            'categories',
            'search',
            'categoryId',
            'subcategoryId',
            'stockScope'
        ));
    }

    /**
     * Export current live stock view (all warehouses matrix or single warehouse) as official PDF.
     */
    public function exportPdf(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            abort(403, 'بخش انبار موجودی غیرفعال شده است.');
        }

        $warehouses = Warehouse::inventory()->active()
            ->with('managers')
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $warehouseId = $request->filled('warehouse_id') ? (int)$request->input('warehouse_id') : null;
        $selectedWarehouse = $warehouseId ? $warehouses->firstWhere('id', $warehouseId) : null;
        if ($warehouseId && !$selectedWarehouse) {
            $warehouseId = null;
        }

        $search = trim((string)$request->input('search', ''));
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');
        $stockScope = $request->input('stock_scope', 'in_stock');

        $items = $this->buildFilteredQuery($warehouseId, $search, $categoryId, $subcategoryId, $stockScope)
            ->limit(600)
            ->get();

        $metrics = $this->valuationService->getWarehouseSnapshotMetrics($warehouseId);

        $filename = $selectedWarehouse
            ? ('stock-' . ($selectedWarehouse->code ?: $selectedWarehouse->id) . '-' . date('Y-m-d') . '.pdf')
            : ('stock-all-warehouses-' . date('Y-m-d') . '.pdf');

        return app(WarehousePdfService::class)->downloadInline(
            'warehouse::pdf.stock_levels',
            compact('warehouses', 'selectedWarehouse', 'warehouseId', 'items', 'metrics', 'stockScope'),
            $filename,
            ['landscape' => !$selectedWarehouse]
        );
    }

    protected function buildFilteredQuery(
        ?int $warehouseId,
        string $search,
        $categoryId,
        $subcategoryId,
        string $stockScope
    ) {
        $today = now()->toDateString();
        $inNinetyDays = now()->addDays(90)->toDateString();

        return WarehouseItem::with([
            'category',
            'subcategory',
            'creator',
            'activeBatches' => function ($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
                $q->with(['warehouse', 'creator'])->orderBy('purchase_date')->orderBy('id');
            },
        ])
            ->active()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('system_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('item_type', 'like', "%{$search}%")
                        ->orWhere('model_spec', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($subcategoryId, fn($q) => $q->where('subcategory_id', $subcategoryId))
            ->when($stockScope === 'in_stock', function ($q) use ($warehouseId) {
                $q->whereHas('activeBatches', function ($b) use ($warehouseId) {
                    if ($warehouseId) {
                        $b->where('warehouse_id', $warehouseId);
                    }
                });
            })
            ->when($stockScope === 'zero', function ($q) use ($warehouseId) {
                $q->whereDoesntHave('activeBatches', function ($b) use ($warehouseId) {
                    if ($warehouseId) {
                        $b->where('warehouse_id', $warehouseId);
                    }
                });
            })
            ->when($stockScope === 'expiring', function ($q) use ($warehouseId, $inNinetyDays) {
                $q->whereHas('activeBatches', function ($b) use ($warehouseId, $inNinetyDays) {
                    if ($warehouseId) {
                        $b->where('warehouse_id', $warehouseId);
                    }
                    $b->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<=', $inNinetyDays);
                });
            })
            ->withSum(['activeBatches as filtered_stock_sum' => function ($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            }], 'remaining_quantity')
            ->orderByDesc('filtered_stock_sum')
            ->orderBy('name');
    }
}
