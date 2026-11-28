<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Warehouse\App\Services\WarehouseCodeService;
use Modules\Warehouse\App\Services\WarehouseExcelImportService;
use Modules\Warehouse\App\Services\WarehousePdfService;
use Modules\Warehouse\App\Services\WarehousePurchaseService;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehousePurchaseReceipt;
use Modules\Warehouse\Entities\WarehousePurchaseReceiptItem;
use Modules\Warehouse\Entities\WarehouseSetting;

class PurchaseController extends Controller
{
    protected WarehouseCodeService $codeService;
    protected WarehousePurchaseService $purchaseService;
    protected WarehouseExcelImportService $importService;

    public function __construct(
        WarehouseCodeService $codeService,
        WarehousePurchaseService $purchaseService,
        WarehouseExcelImportService $importService
    ) {
        $this->codeService = $codeService;
        $this->purchaseService = $purchaseService;
        $this->importService = $importService;
    }

    public function index(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی در تنظیمات غیرفعال شده است.');
        }

        $search = trim((string)$request->input('search', ''));
        $warehouseId = $request->input('warehouse_id');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $gregFrom = null;
        $gregTo = null;
        try {
            $gregFrom = $dateFrom ? $this->purchaseService->parseDate($dateFrom) : null;
            $gregTo = $dateTo ? $this->purchaseService->parseDate($dateTo) : null;
        } catch (\Throwable $e) {
            // Keep query safe if user typed incomplete date
        }

        $query = WarehousePurchaseReceipt::with([
            'warehouse',
            'creator',
            'items.item',
            'items.warehouse',
            'items.batch',
            'batches',
        ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('invoice_number', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('items.item', function ($itemQ) use ($search) {
                            $itemQ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('system_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where(function ($sub) use ($warehouseId) {
                    $sub->where('warehouse_id', $warehouseId)
                        ->orWhereHas('items', fn($i) => $i->where('warehouse_id', $warehouseId));
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($gregFrom, fn($q) => $q->whereDate('purchase_date', '>=', $gregFrom))
            ->when($gregTo, fn($q) => $q->whereDate('purchase_date', '<=', $gregTo));

        $receipts = $query->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Summary metrics for registered receipts
        $baseMetricsQuery = WarehousePurchaseReceipt::registered()
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where(function ($sub) use ($warehouseId) {
                    $sub->where('warehouse_id', $warehouseId)
                        ->orWhereHas('items', fn($i) => $i->where('warehouse_id', $warehouseId));
                });
            })
            ->when($gregFrom, fn($q) => $q->whereDate('purchase_date', '>=', $gregFrom))
            ->when($gregTo, fn($q) => $q->whereDate('purchase_date', '<=', $gregTo));

        $metrics = [
            'receipts_count' => (clone $baseMetricsQuery)->count(),
            'total_quantity' => (float)(clone $baseMetricsQuery)->sum('total_quantity'),
            'total_amount' => (float)(clone $baseMetricsQuery)->sum('total_amount'),
            'month_amount' => (float)WarehousePurchaseReceipt::registered()
                ->whereDate('purchase_date', '>=', now()->startOfMonth()->toDateString())
                ->sum('total_amount'),
        ];

        $warehouses = Warehouse::inventory()->active()->orderByDesc('is_main')->orderBy('name')->get();

        return view('warehouse::purchases.index', compact(
            'receipts',
            'metrics',
            'warehouses',
            'search',
            'warehouseId',
            'status',
            'dateFrom',
            'dateTo'
        ));
    }

    public function create(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی در تنظیمات غیرفعال شده است.');
        }

        $receipt = new WarehousePurchaseReceipt([
            'receipt_number' => $this->codeService->generatePurchaseReceiptCode(),
            'status' => 'registered',
        ]);

        $warehouses = Warehouse::inventory()->active()->orderByDesc('is_main')->orderBy('name')->get();
        $mainWarehouse = Warehouse::inventory()->main()->active()->first() ?? $warehouses->first();
        $todayJalali = function_exists('jdate') ? jdate('now')->format('Y/m/d') : now()->format('Y-m-d');

        $suppliers = WarehousePurchaseReceipt::whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct()
            ->orderBy('supplier_name')
            ->limit(50)
            ->pluck('supplier_name');

        // Optional prefill if opened from a specific item page or reorder alert (?item_id=123&suggested_qty=5)
        $prefillItem = null;
        if ($request->filled('item_id')) {
            $prefill = WarehouseItem::with('activeBatches')->find($request->input('item_id'));
            if ($prefill) {
                $prefillItem = $this->formatItemForAutocomplete($prefill);
                if ($request->filled('suggested_qty')) {
                    $suggestedQty = (float)str_replace([',', ' '], '', (string)$request->input('suggested_qty'));
                    if ($suggestedQty > 0) {
                        $prefillItem['suggested_quantity'] = max(1, (int)ceil($suggestedQty));
                    }
                }
            }
        }

        return view('warehouse::purchases.create', compact(
            'receipt',
            'warehouses',
            'mainWarehouse',
            'todayJalali',
            'suppliers',
            'prefillItem'
        ));
    }

    /**
     * Fast JSON search endpoint for items in purchase lines.
     */
    public function searchItems(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $limit = min(25, max(5, (int)$request->input('limit', 15)));

        $query = WarehouseItem::with('activeBatches')->active();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('system_code', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('model_spec', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn($item) => $this->formatItemForAutocomplete($item))
            ->values();

        return response()->json([
            'results' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request = $this->normalizeNumericInputs($request);

        $validated = $request->validate([
            'purchase_date' => 'required|string|max:30',
            'invoice_number' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|exists:warehouse_warehouses,id',
            'supplier_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'update_current_prices' => 'sometimes|boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:warehouse_items,id',
            'items.*.warehouse_id' => 'nullable|exists:warehouse_warehouses,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|string|max:30',
            'items.*.notes' => 'nullable|string|max:500',
        ], [
            'purchase_date.required' => 'تاریخ خرید الزامی است.',
            'items.required' => 'حداقل یک قلم کالا باید به رسید اضافه شود.',
            'items.min' => 'حداقل یک قلم کالا باید به رسید اضافه شود.',
            'items.*.item_id.required' => 'انتخاب کالا برای تمام ردیف‌ها الزامی است.',
            'items.*.quantity.required' => 'تعداد در تمام ردیف‌ها الزامی است.',
            'items.*.quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'items.*.quantity.min' => 'تعداد هر ردیف باید حداقل ۱ باشد.',
            'items.*.unit_price.required' => 'فی خرید برای تمام ردیف‌ها الزامی است.',
            'items.*.unit_price.numeric' => 'فی خرید باید عددی معتبر باشد.',
        ]);

        try {
            $receipt = $this->purchaseService->createReceipt(
                [
                    'purchase_date' => $validated['purchase_date'],
                    'invoice_number' => $validated['invoice_number'] ?? null,
                    'warehouse_id' => $validated['warehouse_id'] ?? null,
                    'supplier_name' => $validated['supplier_name'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ],
                $validated['items'],
                $request->boolean('update_current_prices')
            );

            return redirect()->route('admin.warehouse.purchases.show', $receipt)
                ->with('success', "رسید خرید «{$receipt->receipt_number}» با {$receipt->total_items_count} قلم کالا با موفقیت ثبت و به انبار وارد شد.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'خطا در ثبت رسید خرید: ' . $e->getMessage());
        }
    }

    public function show(WarehousePurchaseReceipt $purchase)
    {
        $purchase->load([
            'warehouse',
            'creator',
            'items.item.category',
            'items.item.subcategory',
            'items.warehouse',
            'items.batch',
            'batches',
        ]);

        return view('warehouse::purchases.show', compact('purchase'));
    }

    public function exportPdf(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            abort(403, 'بخش انبار موجودی غیرفعال شده است.');
        }

        $search = trim((string)$request->input('search', ''));
        $warehouseId = $request->input('warehouse_id');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $gregFrom = null;
        $gregTo = null;
        try {
            $gregFrom = $dateFrom ? $this->purchaseService->parseDate($dateFrom) : null;
            $gregTo = $dateTo ? $this->purchaseService->parseDate($dateTo) : null;
        } catch (\Throwable $e) {
        }

        $receipts = WarehousePurchaseReceipt::with(['warehouse', 'creator'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('invoice_number', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('items.item', function ($itemQ) use ($search) {
                            $itemQ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('system_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where(function ($sub) use ($warehouseId) {
                    $sub->where('warehouse_id', $warehouseId)
                        ->orWhereHas('items', fn($i) => $i->where('warehouse_id', $warehouseId));
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($gregFrom, fn($q) => $q->whereDate('purchase_date', '>=', $gregFrom))
            ->when($gregTo, fn($q) => $q->whereDate('purchase_date', '<=', $gregTo))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $registered = $receipts->where('status', 'registered');
        $metrics = [
            'receipts_count' => $registered->count(),
            'total_quantity' => (float)$registered->sum('total_quantity'),
            'total_amount' => (float)$registered->sum('total_amount'),
        ];

        return app(WarehousePdfService::class)->downloadInline(
            'warehouse::pdf.purchase_list',
            compact('receipts', 'metrics', 'dateFrom', 'dateTo'),
            'warehouse-purchases-' . date('Y-m-d') . '.pdf'
        );
    }

    public function showPdf(WarehousePurchaseReceipt $purchase)
    {
        $purchase->load([
            'warehouse',
            'creator',
            'items.item.category',
            'items.item.subcategory',
            'items.warehouse',
            'items.batch',
            'batches',
        ]);

        return app(WarehousePdfService::class)->downloadInline(
            'warehouse::pdf.purchase_show',
            compact('purchase'),
            'purchase-receipt-' . $purchase->receipt_number . '.pdf'
        );
    }

    public function edit(WarehousePurchaseReceipt $purchase)
    {
        $purchase->load(['items.item.activeBatches', 'items.warehouse', 'batches']);

        if (!$purchase->can_modify) {
            return redirect()->route('admin.warehouse.purchases.show', $purchase)
                ->with('error', 'این رسید به دلیل ابطال یا مصرف بخشی از موجودی پارت‌های آن در انبار، قابل ویرایش نیست.');
        }

        $receipt = $purchase;
        $warehouses = Warehouse::inventory()->active()->orderByDesc('is_main')->orderBy('name')->get();
        $mainWarehouse = Warehouse::inventory()->main()->active()->first() ?? $warehouses->first();
        $todayJalali = function_exists('jdate') && $purchase->purchase_date
            ? jdate($purchase->purchase_date)->format('Y/m/d')
            : now()->format('Y-m-d');

        $suppliers = WarehousePurchaseReceipt::whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct()
            ->orderBy('supplier_name')
            ->limit(50)
            ->pluck('supplier_name');

        $prefillItem = null;

        return view('warehouse::purchases.edit', compact(
            'receipt',
            'warehouses',
            'mainWarehouse',
            'todayJalali',
            'suppliers',
            'prefillItem'
        ));
    }

    public function update(Request $request, WarehousePurchaseReceipt $purchase)
    {
        $request = $this->normalizeNumericInputs($request);

        $validated = $request->validate([
            'purchase_date' => 'required|string|max:30',
            'invoice_number' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|exists:warehouse_warehouses,id',
            'supplier_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'update_current_prices' => 'sometimes|boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:warehouse_items,id',
            'items.*.warehouse_id' => 'nullable|exists:warehouse_warehouses,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|string|max:30',
            'items.*.notes' => 'nullable|string|max:500',
        ], [
            'purchase_date.required' => 'تاریخ خرید الزامی است.',
            'items.required' => 'حداقل یک قلم کالا باید در رسید ثبت شود.',
            'items.*.item_id.required' => 'انتخاب کالا برای تمام ردیف‌ها الزامی است.',
            'items.*.quantity.required' => 'تعداد در تمام ردیف‌ها الزامی است.',
            'items.*.quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'items.*.quantity.min' => 'تعداد هر ردیف باید حداقل ۱ باشد.',
            'items.*.unit_price.required' => 'فی خرید برای تمام ردیف‌ها الزامی است.',
        ]);

        try {
            $updated = $this->purchaseService->updateReceipt(
                $purchase,
                [
                    'purchase_date' => $validated['purchase_date'],
                    'invoice_number' => $validated['invoice_number'] ?? null,
                    'warehouse_id' => $validated['warehouse_id'] ?? null,
                    'supplier_name' => $validated['supplier_name'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ],
                $validated['items'],
                $request->boolean('update_current_prices')
            );

            return redirect()->route('admin.warehouse.purchases.show', $updated)
                ->with('success', "رسید خرید «{$updated->receipt_number}» با موفقیت ویرایش و موجودی انبار به‌روزرسانی شد.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'خطا در ویرایش رسید خرید: ' . $e->getMessage());
        }
    }

    public function destroy(WarehousePurchaseReceipt $purchase)
    {
        $receiptNumber = $purchase->receipt_number;

        try {
            $this->purchaseService->cancelReceipt($purchase, true);

            return redirect()->route('admin.warehouse.purchases.index')
                ->with('success', "رسید خرید «{$receiptNumber}» با موفقیت ابطال و موجودی پارت‌های مربوطه از انبار برگشت داده شد.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function importForm()
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی در تنظیمات غیرفعال شده است.');
        }

        return view('warehouse::purchases.import');
    }

    public function importSubmit(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            'update_current_prices' => 'sometimes|boolean',
        ], [
            'excel_file.required' => 'لطفاً فایل اکسل ورودی خرید را انتخاب کنید.',
            'excel_file.mimes' => 'فرمت فایل باید xlsx یا xls باشد.',
        ]);

        try {
            $result = $this->importService->importPurchases(
                $request->file('excel_file')->getRealPath(),
                $request->boolean('update_current_prices')
            );

            if ($result['receipts_created'] === 0 && !empty($result['errors'])) {
                return back()->with('error', implode(' | ', array_slice($result['errors'], 0, 5)));
            }

            $msg = "درون‌ریزی با موفقیت انجام شد: {$result['receipts_created']} رسید خرید و {$result['lines_imported']} قلم کالا ثبت گردید.";
            if (!empty($result['skipped_rows'])) {
                $msg .= " ({$result['skipped_rows']} ردیف خالی یا تکراری نادیده گرفته شد.)";
            }

            $redirect = redirect()->route('admin.warehouse.purchases.index')->with('success', $msg);
            if (!empty($result['errors'])) {
                $redirect->with('warning', 'برخی ردیف‌ها ثبت نشدند: ' . implode(' | ', array_slice($result['errors'], 0, 4)));
            }

            return $redirect;
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در پردازش فایل اکسل: ' . $e->getMessage());
        }
    }

    protected function formatItemForAutocomplete(WarehouseItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code,
            'system_code' => $item->system_code,
            'unit' => $item->unit,
            'current_stock' => (int)round($item->total_stock),
            'last_purchase_price' => $item->last_purchase_price ? (int)round((float)$item->last_purchase_price) : 0,
            'current_price' => $item->current_price ? (int)round((float)$item->current_price) : 0,
            'default_warehouse_id' => $item->default_warehouse_id,
            'has_expiry' => (bool)$item->has_expiry,
        ];
    }

    protected function normalizeNumericInputs(Request $request): Request
    {
        $cleanNumber = function ($val) {
            if ($val === null || $val === '') {
                return null;
            }
            $cleaned = $this->purchaseService->normalizeDigits((string)$val);
            $cleaned = str_replace([',', '،', ' '], '', $cleaned);
            return $cleaned !== '' ? $cleaned : null;
        };

        $items = $request->input('items', []);
        if (is_array($items)) {
            foreach ($items as $idx => $line) {
                if (isset($line['quantity'])) {
                    $items[$idx]['quantity'] = $cleanNumber($line['quantity']);
                }
                if (isset($line['unit_price'])) {
                    $items[$idx]['unit_price'] = $cleanNumber($line['unit_price']);
                }
            }
            $request->merge(['items' => $items]);
        }

        return $request;
    }
}
