<?php

namespace Modules\Warehouse\App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Warehouse\App\Services\WarehouseCodeService;
use Modules\Warehouse\App\Services\WarehouseExcelImportService;
use Modules\Warehouse\App\Services\WarehousePdfService;
use Modules\Warehouse\App\Services\WarehousePurchaseService;
use Modules\Warehouse\App\Services\WarehouseTransferService;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseItemBatch;
use Modules\Warehouse\Entities\WarehouseSetting;
use Modules\Warehouse\Entities\WarehouseTransferRequest;
use Modules\Warehouse\Support\WarehouseUserVisibility;

class TransferController extends Controller
{
    protected WarehouseCodeService $codeService;
    protected WarehouseTransferService $transferService;
    protected WarehousePurchaseService $purchaseService;
    protected WarehouseExcelImportService $importService;

    public function __construct(
        WarehouseCodeService $codeService,
        WarehouseTransferService $transferService,
        WarehousePurchaseService $purchaseService,
        WarehouseExcelImportService $importService
    ) {
        $this->codeService = $codeService;
        $this->transferService = $transferService;
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
        $sourceId = $request->input('source_warehouse_id');
        $destinationId = $request->input('destination_warehouse_id');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $gregFrom = null;
        $gregTo = null;
        try {
            $gregFrom = $dateFrom ? $this->purchaseService->parseDate($dateFrom) : null;
            $gregTo = $dateTo ? $this->purchaseService->parseDate($dateTo) : null;
        } catch (\Throwable $e) {
            // Keep query safe if user typed partial date
        }

        $query = WarehouseTransferRequest::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'requesterUser',
            'creator',
            'approver',
            'items.item',
            'transferredBatches',
        ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('transfer_number', 'like', "%{$search}%")
                        ->orWhere('request_number', 'like', "%{$search}%")
                        ->orWhere(function ($rq) use ($search) {
                            $rq->where('requester_name', 'like', "%{$search}%");
                            if (!WarehouseUserVisibility::isSuperAdmin()) {
                                $rq->whereDoesntHave('requesterUser.roles', fn($r) => $r->where('name', 'super-admin'));
                            }
                        })
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('items.item', function ($itemQ) use ($search) {
                            $itemQ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('system_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($sourceId, fn($q) => $q->where('source_warehouse_id', $sourceId))
            ->when($destinationId, fn($q) => $q->where('destination_warehouse_id', $destinationId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($gregFrom, fn($q) => $q->whereDate('request_date', '>=', $gregFrom))
            ->when($gregTo, fn($q) => $q->whereDate('request_date', '<=', $gregTo));

        $transfers = $query->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $baseQuery = WarehouseTransferRequest::query()
            ->when($sourceId, fn($q) => $q->where('source_warehouse_id', $sourceId))
            ->when($destinationId, fn($q) => $q->where('destination_warehouse_id', $destinationId))
            ->when($gregFrom, fn($q) => $q->whereDate('request_date', '>=', $gregFrom))
            ->when($gregTo, fn($q) => $q->whereDate('request_date', '<=', $gregTo));

        $metrics = [
            'total_count' => (clone $baseQuery)->whereIn('status', ['pending', 'delivered'])->count(),
            'pending_count' => (clone $baseQuery)->pending()->count(),
            'delivered_units' => (float)(clone $baseQuery)->delivered()->sum('total_delivered_quantity'),
            'delivered_cost_value' => (float)(clone $baseQuery)->delivered()->sum('total_cost_value'),
        ];

        $warehouses = Warehouse::inventory()->active()->orderByDesc('is_main')->orderBy('name')->get();

        return view('warehouse::transfers.index', compact(
            'transfers',
            'metrics',
            'warehouses',
            'search',
            'sourceId',
            'destinationId',
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

        $transfer = new WarehouseTransferRequest([
            'transfer_number' => $this->codeService->generateTransferRequestCode(),
            'request_number' => $this->codeService->generateRequestNumber(),
            'status' => 'delivered',
        ]);

        $warehouses = Warehouse::inventory()->active()
            ->with('managers:id,name,email,mobile,profile_photo_path')
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $mainWarehouse = $warehouses->firstWhere('is_main', true) ?? $warehouses->first();
        $subWarehouses = $warehouses->filter(fn($w) => !$mainWarehouse || $w->id !== $mainWarehouse->id)->values();

        $defaultSourceId = (int)($request->input('source_warehouse_id') ?: ($mainWarehouse?->id ?? 0));
        $defaultDestId = (int)($request->input('destination_warehouse_id') ?: ($subWarehouses->first()?->id ?? 0));

        $todayJalali = function_exists('jdate') ? jdate('now')->format('Y/m/d') : now()->format('Y-m-d');

        $canSelectRequester = WarehouseUserVisibility::canSelectOtherRequester();
        $currentUser = auth()->user();
        $lockedRequester = $currentUser ? WarehouseUserVisibility::formatUserOption($currentUser) : null;

        $warehouseManagersMap = $warehouses->mapWithKeys(function ($w) {
            $visibleManagers = WarehouseUserVisibility::filterVisibleManagers($w->managers)
                ->map(fn($u) => WarehouseUserVisibility::formatUserOption($u))
                ->values()
                ->toArray();
            return [$w->id => $visibleManagers];
        });

        $prefillItem = null;
        if ($request->filled('item_id')) {
            $itemModel = WarehouseItem::with('activeBatches')->find($request->input('item_id'));
            if ($itemModel) {
                $prefillItem = $this->formatItemForAutocomplete($itemModel, $defaultSourceId ?: null, $defaultDestId ?: null);
            }
        }

        return view('warehouse::transfers.create', compact(
            'transfer',
            'warehouses',
            'mainWarehouse',
            'defaultSourceId',
            'defaultDestId',
            'todayJalali',
            'canSelectRequester',
            'lockedRequester',
            'warehouseManagersMap',
            'prefillItem'
        ));
    }

    public function searchItems(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $sourceWarehouseId = $request->filled('source_warehouse_id') ? (int)$request->input('source_warehouse_id') : null;
        $destWarehouseId = $request->filled('destination_warehouse_id') ? (int)$request->input('destination_warehouse_id') : null;
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
            ->map(fn($item) => $this->formatItemForAutocomplete($item, $sourceWarehouseId, $destWarehouseId))
            ->values();

        return response()->json([
            'results' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request = $this->normalizeNumericInputs($request);

        $validated = $request->validate([
            'needed_date' => 'nullable|string|max:30',
            'source_warehouse_id' => 'required|exists:warehouse_warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouse_warehouses,id|different:source_warehouse_id',
            'requester_user_id' => 'nullable|exists:users,id',
            'requester_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'submission_mode' => 'nullable|in:deliver,pending',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:warehouse_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
        ], [
            'source_warehouse_id.required' => 'انتخاب انبار مبدأ الزامی است.',
            'destination_warehouse_id.required' => 'انتخاب انبارک مقصد الزامی است.',
            'destination_warehouse_id.different' => 'انبار مبدأ و انبارک مقصد نمی‌توانند یکسان باشند.',
            'items.required' => 'حداقل یک قلم کالا باید در درخواست ثبت شود.',
            'items.min' => 'حداقل یک قلم کالا باید در درخواست ثبت شود.',
            'items.*.item_id.required' => 'انتخاب کالا برای تمام ردیف‌ها الزامی است.',
            'items.*.quantity.required' => 'تعداد در تمام ردیف‌ها الزامی است.',
            'items.*.quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'items.*.quantity.min' => 'تعداد هر ردیف باید حداقل ۱ باشد.',
        ]);

        // Role-based lock & super-admin visibility guard on requester_user_id
        $currentUser = auth()->user();
        if (!WarehouseUserVisibility::canSelectOtherRequester($currentUser)) {
            $requesterUserId = $currentUser?->id;
            $requesterName = $currentUser?->name;
        } elseif (!empty($validated['requester_user_id'])) {
            $selectedRequester = User::find((int)$validated['requester_user_id']);
            if ($selectedRequester && !WarehouseUserVisibility::canSeeUser($selectedRequester, $currentUser)) {
                return back()->withInput()->withErrors([
                    'requester_user_id' => 'امکان انتخاب این کاربر وجود ندارد.',
                ]);
            }
            $requesterUserId = $selectedRequester?->id;
            $requesterName = $selectedRequester?->name ?? ($validated['requester_name'] ?? null);
        } else {
            $requesterUserId = $currentUser?->id;
            $requesterName = !empty($validated['requester_name']) ? $validated['requester_name'] : $currentUser?->name;
        }

        $deliverImmediately = ($validated['submission_mode'] ?? 'deliver') === 'deliver';

        try {
            // Security & audit integrity: request_date and request_number are always generated server-side.
            $transfer = $this->transferService->createTransfer([
                'request_date' => now()->toDateString(),
                'needed_date' => $validated['needed_date'] ?? null,
                'source_warehouse_id' => $validated['source_warehouse_id'],
                'destination_warehouse_id' => $validated['destination_warehouse_id'],
                'requester_user_id' => $requesterUserId,
                'requester_name' => $requesterName,
                'notes' => $validated['notes'] ?? null,
            ], $validated['items'], $deliverImmediately);

            $msg = $deliverImmediately
                ? "حواله انتقال «{$transfer->transfer_number}» با {$transfer->total_items_count} قلم کالا ثبت و موجودی به انبارک مقصد منتقل شد."
                : "درخواست کالا «{$transfer->transfer_number}» ثبت شد و در انتظار تایید و تحویل انباردار قرار گرفت.";

            return redirect()->route('admin.warehouse.transfers.show', $transfer)
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'خطا در ثبت درخواست / حواله: ' . $e->getMessage());
        }
    }

    public function show(WarehouseTransferRequest $transfer)
    {
        $transfer->load([
            'sourceWarehouse',
            'destinationWarehouse',
            'requesterUser',
            'creator',
            'approver',
            'items.item.category',
            'items.item.subcategory',
            'transferredBatches.warehouse',
        ]);

        // Calculate live source stock for pending fulfillment view
        $sourceStocks = [];
        if ($transfer->status === 'pending' && $transfer->source_warehouse_id) {
            $itemIds = $transfer->items->pluck('item_id')->unique()->toArray();
            $sourceStocks = WarehouseItemBatch::where('warehouse_id', $transfer->source_warehouse_id)
                ->whereIn('item_id', $itemIds)
                ->where('remaining_quantity', '>', 0)
                ->selectRaw('item_id, SUM(remaining_quantity) as total_avail')
                ->groupBy('item_id')
                ->pluck('total_avail', 'item_id')
                ->map(fn($v) => (int)round((float)$v))
                ->toArray();
        }

        return view('warehouse::transfers.show', compact('transfer', 'sourceStocks'));
    }

    public function exportPdf(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            abort(403, 'بخش انبار موجودی غیرفعال شده است.');
        }

        $search = trim((string)$request->input('search', ''));
        $sourceId = $request->input('source_warehouse_id');
        $destinationId = $request->input('destination_warehouse_id');
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

        $transfers = WarehouseTransferRequest::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'requesterUser',
            'creator',
            'approver',
        ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('transfer_number', 'like', "%{$search}%")
                        ->orWhere('request_number', 'like', "%{$search}%")
                        ->orWhere(function ($rq) use ($search) {
                            $rq->where('requester_name', 'like', "%{$search}%");
                            if (!WarehouseUserVisibility::isSuperAdmin()) {
                                $rq->whereDoesntHave('requesterUser.roles', fn($r) => $r->where('name', 'super-admin'));
                            }
                        })
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('items.item', function ($itemQ) use ($search) {
                            $itemQ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('system_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($sourceId, fn($q) => $q->where('source_warehouse_id', $sourceId))
            ->when($destinationId, fn($q) => $q->where('destination_warehouse_id', $destinationId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($gregFrom, fn($q) => $q->whereDate('request_date', '>=', $gregFrom))
            ->when($gregTo, fn($q) => $q->whereDate('request_date', '<=', $gregTo))
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $metrics = [
            'total_count' => $transfers->whereIn('status', ['pending', 'delivered'])->count(),
            'pending_count' => $transfers->where('status', 'pending')->count(),
            'delivered_units' => (float)$transfers->where('status', 'delivered')->sum('total_delivered_quantity'),
            'delivered_cost_value' => (float)$transfers->where('status', 'delivered')->sum('total_cost_value'),
        ];

        return app(WarehousePdfService::class)->downloadInline(
            'warehouse::pdf.transfer_list',
            compact('transfers', 'metrics'),
            'warehouse-transfers-' . date('Y-m-d') . '.pdf',
            ['landscape' => true]
        );
    }

    public function showPdf(WarehouseTransferRequest $transfer)
    {
        $transfer->load([
            'sourceWarehouse',
            'destinationWarehouse',
            'requesterUser',
            'creator',
            'approver',
            'items.item.category',
            'items.item.subcategory',
            'transferredBatches.item',
        ]);

        return app(WarehousePdfService::class)->downloadInline(
            'warehouse::pdf.transfer_show',
            compact('transfer'),
            'transfer-voucher-' . $transfer->transfer_number . '.pdf'
        );
    }

    public function fulfill(Request $request, WarehouseTransferRequest $transfer)
    {
        $deliveredMap = $request->input('delivered_quantities', []);
        if (is_array($deliveredMap)) {
            foreach ($deliveredMap as $lineId => $val) {
                $clean = $this->purchaseService->normalizeDigits((string)$val);
                $clean = str_replace([',', '،', ' '], '', $clean);
                $deliveredMap[$lineId] = $clean !== '' ? (int)$clean : 0;
            }
        } else {
            $deliveredMap = [];
        }

        try {
            $updated = $this->transferService->fulfillPendingTransfer(
                $transfer,
                $deliveredMap,
                $request->input('notes')
            );

            return redirect()->route('admin.warehouse.transfers.show', $updated)
                ->with('success', "درخواست «{$updated->transfer_number}» تایید و انتقال پارت‌های کالا با موفقیت انجام شد.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'خطا در تایید و تحویل درخواست: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, WarehouseTransferRequest $transfer)
    {
        try {
            $rejected = $this->transferService->rejectPendingTransfer($transfer, $request->input('reason'));

            return redirect()->route('admin.warehouse.transfers.show', $rejected)
                ->with('success', "درخواست «{$rejected->transfer_number}» با موفقیت رد شد.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(WarehouseTransferRequest $transfer)
    {
        $transferNumber = $transfer->transfer_number;

        try {
            $this->transferService->cancelTransfer($transfer, true);

            return redirect()->route('admin.warehouse.transfers.index')
                ->with('success', "حواله «{$transferNumber}» با موفقیت ابطال و در صورت انتقال، موجودی پارت‌ها به انبار مبدأ بازگردانده شد.");
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

        return view('warehouse::transfers.import');
    }

    public function importSubmit(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            'submission_mode' => 'nullable|in:deliver,pending',
        ], [
            'excel_file.required' => 'لطفاً فایل اکسل درخواست کالا را انتخاب کنید.',
            'excel_file.mimes' => 'فرمت فایل باید xlsx یا xls باشد.',
        ]);

        $deliverImmediately = $request->input('submission_mode', 'deliver') === 'deliver';

        try {
            $result = $this->importService->importTransfers(
                $request->file('excel_file')->getRealPath(),
                $deliverImmediately
            );

            if ($result['transfers_created'] === 0 && !empty($result['errors'])) {
                return back()->with('error', implode(' | ', array_slice($result['errors'], 0, 5)));
            }

            $msg = "درون‌ریزی با موفقیت انجام شد: {$result['transfers_created']} سند درخواست/انتقال و {$result['lines_imported']} ردیف کالا ثبت شد.";
            if (!empty($result['skipped_rows'])) {
                $msg .= " ({$result['skipped_rows']} ردیف خالی یا تکراری نادیده گرفته شد.)";
            }

            $redirect = redirect()->route('admin.warehouse.transfers.index')->with('success', $msg);
            if (!empty($result['errors'])) {
                $redirect->with('warning', 'برخی ردیف‌ها ثبت نشدند: ' . implode(' | ', array_slice($result['errors'], 0, 4)));
            }

            return $redirect;
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در پردازش فایل اکسل: ' . $e->getMessage());
        }
    }

    protected function formatItemForAutocomplete(WarehouseItem $item, ?int $sourceWarehouseId = null, ?int $destWarehouseId = null): array
    {
        $batches = $item->relationLoaded('activeBatches') ? $item->activeBatches : $item->activeBatches()->get();

        $sourceStock = $sourceWarehouseId
            ? (int)round((float)$batches->where('warehouse_id', $sourceWarehouseId)->sum('remaining_quantity'))
            : (int)round((float)$batches->sum('remaining_quantity'));

        $destStock = $destWarehouseId
            ? (int)round((float)$batches->where('warehouse_id', $destWarehouseId)->sum('remaining_quantity'))
            : 0;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code,
            'system_code' => $item->system_code,
            'unit' => $item->unit,
            'total_stock' => (int)round((float)$batches->sum('remaining_quantity')),
            'source_stock' => $sourceStock,
            'destination_stock' => $destStock,
        ];
    }

    protected function normalizeNumericInputs(Request $request): Request
    {
        $items = $request->input('items', []);
        if (is_array($items)) {
            foreach ($items as $idx => $line) {
                if (isset($line['quantity'])) {
                    $cleaned = $this->purchaseService->normalizeDigits((string)$line['quantity']);
                    $cleaned = str_replace([',', '،', ' '], '', $cleaned);
                    $items[$idx]['quantity'] = $cleaned !== '' ? $cleaned : null;
                }
            }
            $request->merge(['items' => $items]);
        }

        return $request;
    }
}
