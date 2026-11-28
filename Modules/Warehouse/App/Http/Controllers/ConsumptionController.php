<?php

namespace Modules\Warehouse\App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Clients\Entities\Client;
use Modules\Warehouse\App\Services\WarehouseConsumptionService;
use Modules\Warehouse\App\Services\WarehouseExcelImportService;
use Modules\Warehouse\App\Services\WarehousePdfService;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseIssueDocument;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseSetting;
use Modules\Warehouse\Support\WarehouseUserVisibility;

class ConsumptionController extends Controller
{
    protected WarehouseConsumptionService $consumptionService;

    public function __construct(WarehouseConsumptionService $consumptionService)
    {
        $this->consumptionService = $consumptionService;
    }

    public function index(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی در تنظیمات غیرفعال شده است.');
        }

        $search = trim((string)$request->input('search', ''));
        $warehouseId = $request->input('warehouse_id');
        $issueType = $request->input('issue_type');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $documents = $this->buildFilteredQuery($search, $warehouseId, $issueType, $status, $dateFrom, $dateTo)
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::inventory()->active()->orderByDesc('is_main')->orderBy('name')->get();

        $registeredQuery = WarehouseIssueDocument::where('status', 'registered');
        $monthStart = now()->startOfMonth()->toDateString();
        $todayStr = now()->toDateString();

        $metrics = [
            'total_documents' => (clone $registeredQuery)->count(),
            'patient_documents' => (clone $registeredQuery)->where('issue_type', WarehouseIssueDocument::TYPE_PATIENT_CONSUMPTION)->count(),
            'this_month_units' => (float)(clone $registeredQuery)->whereDate('issue_date', '>=', $monthStart)->sum('total_quantity'),
            'this_month_cost' => (float)(clone $registeredQuery)->whereDate('issue_date', '>=', $monthStart)->sum('total_cost_value'),
            'today_documents' => (clone $registeredQuery)->whereDate('issue_date', $todayStr)->count(),
        ];

        return view('warehouse::consumptions.index', compact(
            'documents',
            'warehouses',
            'metrics',
            'search',
            'warehouseId',
            'issueType',
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

        $warehouses = Warehouse::inventory()->active()->orderByDesc('is_main')->orderBy('name')->get();
        $defaultWarehouseId = $request->filled('warehouse_id')
            ? (int)$request->input('warehouse_id')
            : ($warehouses->first()?->id ?? null);

        $todayJalali = function_exists('jdate') ? jdate('now')->format('Y/m/d') : now()->format('Y-m-d');

        $prefillItem = null;
        if ($request->filled('item_id')) {
            $item = WarehouseItem::with('activeBatches')->find($request->input('item_id'));
            if ($item) {
                $prefillItem = $this->formatItemForAutocomplete($item, $defaultWarehouseId);
            }
        }

        $prefillClient = null;
        if ($request->filled('client_id')) {
            $client = Client::find($request->input('client_id'));
            if ($client) {
                $prefillClient = [
                    'id' => $client->id,
                    'full_name' => $client->full_name,
                    'case_number' => $client->case_number,
                    'phone' => $client->phone,
                    'national_code' => $client->national_code,
                ];
            }
        }

        return view('warehouse::consumptions.create', compact(
            'warehouses',
            'defaultWarehouseId',
            'todayJalali',
            'prefillItem',
            'prefillClient'
        ));
    }

    public function store(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            abort(403, 'بخش انبار موجودی غیرفعال شده است.');
        }

        $itemsInput = $request->input('items', []);
        if (is_array($itemsInput)) {
            foreach ($itemsInput as $idx => $line) {
                if (isset($line['quantity'])) {
                    $qtyClean = str_replace([',', '،', ' '], '', (string)$line['quantity']);
                    $itemsInput[$idx]['quantity'] = $qtyClean !== '' ? (float)$qtyClean : null;
                }
            }
            $request->merge(['items' => $itemsInput]);
        }

        $validated = $request->validate([
            'issue_date' => ['required', 'string', 'max:30'],
            'issue_type' => ['required', 'in:patient_consumption,department_consumption,waste_expired,other'],
            'source_warehouse_id' => ['required', 'exists:warehouse_warehouses,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'patient_name' => ['nullable', 'string', 'max:190'],
            'patient_case_number' => ['nullable', 'string', 'max:100'],
            'consumer_user_id' => ['nullable', 'exists:users,id'],
            'consumer_name' => ['nullable', 'string', 'max:190'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:warehouse_items,id'],
            'items.*.warehouse_id' => ['nullable', 'exists:warehouse_warehouses,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ], [
            'issue_date.required' => 'تاریخ خروج / مصرف الزامی است.',
            'source_warehouse_id.required' => 'انتخاب انبار یا انبارک مبدأ الزامی است.',
            'items.required' => 'حداقل یک قلم کالا باید در سند ثبت شود.',
            'items.*.item_id.required' => 'انتخاب کالا در تمامی ردیف‌ها الزامی است.',
            'items.*.quantity.required' => 'تعداد مصرفی در تمامی ردیف‌ها الزامی است.',
            'items.*.quantity.min' => 'تعداد هر ردیف باید بیشتر از صفر باشد.',
        ]);

        $consumerUserId = null;
        $consumerName = $validated['consumer_name'] ?? null;
        if (!empty($validated['consumer_user_id'])) {
            $consumerUser = User::find((int)$validated['consumer_user_id']);
            if ($consumerUser && !WarehouseUserVisibility::canSeeUser($consumerUser)) {
                return back()->withInput()->withErrors([
                    'consumer_user_id' => 'امکان انتخاب این کاربر وجود ندارد.',
                ]);
            }
            $consumerUserId = $consumerUser?->id;
            $consumerName = $consumerUser?->name ?? $consumerName;
        }

        try {
            $document = $this->consumptionService->createIssue([
                'issue_date' => $validated['issue_date'],
                'issue_type' => $validated['issue_type'],
                'source_warehouse_id' => $validated['source_warehouse_id'],
                'client_id' => $validated['client_id'] ?? null,
                'patient_name' => $validated['patient_name'] ?? null,
                'patient_case_number' => $validated['patient_case_number'] ?? null,
                'consumer_user_id' => $consumerUserId,
                'consumer_name' => $consumerName,
                'notes' => $validated['notes'] ?? null,
            ], $validated['items']);

            return redirect()->route('admin.warehouse.consumptions.show', $document)
                ->with('success', "سند خروج و مصرف «{$document->issue_number}» با {$document->items_count} قلم کالا ثبت و از موجودی کسر شد.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'خطا در ثبت خروج و مصرف کالا: ' . $e->getMessage());
        }
    }

    public function show(WarehouseIssueDocument $consumption)
    {
        $consumption->load([
            'sourceWarehouse',
            'client',
            'consumerUser',
            'creator',
            'cancelledByUser',
            'items.item.category',
            'items.item.subcategory',
            'items.warehouse',
            'items.batchDeductions.batch',
        ]);

        return view('warehouse::consumptions.show', compact('consumption'));
    }

    public function destroy(WarehouseIssueDocument $consumption)
    {
        try {
            $this->consumptionService->cancelIssue($consumption);

            return redirect()->route('admin.warehouse.consumptions.show', $consumption)
                ->with('success', "سند «{$consumption->issue_number}» ابطال و تمامی مقادیر کسرشده به پارت‌های اصلی و انبار مبدأ بازگردانده شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در ابطال سند: ' . $e->getMessage());
        }
    }

    public function searchItems(Request $request)
    {
        $term = trim((string)$request->input('q', ''));
        $warehouseId = $request->filled('warehouse_id') ? (int)$request->input('warehouse_id') : null;

        if ($term === '') {
            return response()->json(['items' => []]);
        }

        $items = WarehouseItem::with('activeBatches')
            ->active()
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('system_code', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->map(fn($item) => $this->formatItemForAutocomplete($item, $warehouseId));

        return response()->json(['items' => $items]);
    }

    public function searchClients(Request $request)
    {
        $term = trim((string)$request->input('q', ''));
        if ($term === '') {
            return response()->json(['clients' => []]);
        }

        $clients = Client::query()
            ->where(function ($q) use ($term) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('case_number', 'like', "%{$term}%")
                    ->orWhere('national_code', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            })
            ->select('id', 'full_name', 'case_number', 'national_code', 'phone')
            ->orderBy('full_name')
            ->limit(20)
            ->get()
            ->map(function ($c) {
                $parts = [$c->full_name];
                if ($c->case_number) {
                    $parts[] = 'پرونده: ' . $c->case_number;
                }
                if ($c->national_code) {
                    $parts[] = 'کد ملی: ' . $c->national_code;
                }
                if ($c->phone) {
                    $parts[] = $c->phone;
                }
                return [
                    'id' => $c->id,
                    'full_name' => $c->full_name,
                    'case_number' => $c->case_number,
                    'national_code' => $c->national_code,
                    'phone' => $c->phone,
                    'label' => implode(' — ', $parts),
                ];
            });

        return response()->json(['clients' => $clients]);
    }

    public function importForm()
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی غیرفعال شده است.');
        }

        return view('warehouse::consumptions.import');
    }

    public function importSubmit(Request $request, WarehouseExcelImportService $importService)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ], [
            'excel_file.required' => 'انتخاب فایل اکسل (.xlsx) الزامی است.',
            'excel_file.mimes' => 'فقط فایل‌های اکسل با پسوند .xlsx پشتیبانی می‌شوند.',
        ]);

        try {
            $path = $request->file('excel_file')->getRealPath();
            $res = $importService->importConsumptions($path);

            $msg = "درون‌ریزی خروج و مصرف کالا انجام شد: {$res['documents_created']} سند و {$res['lines_imported']} ردیف ثبت و از موجودی کسر شد.";
            if ($res['skipped_rows'] > 0) {
                $msg .= " ({$res['skipped_rows']} ردیف با تعداد صفر/نامعتبر نادیده گرفته شد.)";
            }

            if (!empty($res['errors'])) {
                return back()->with('warning', $msg)->with('import_errors', $res['errors']);
            }

            return redirect()->route('admin.warehouse.consumptions.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در خواندن فایل اکسل: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            abort(403, 'بخش انبار موجودی غیرفعال شده است.');
        }

        $search = trim((string)$request->input('search', ''));
        $warehouseId = $request->input('warehouse_id');
        $issueType = $request->input('issue_type');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $documents = $this->buildFilteredQuery($search, $warehouseId, $issueType, $status, $dateFrom, $dateTo)
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->limit(400)
            ->get();

        return app(WarehousePdfService::class)->downloadInline(
            'warehouse::pdf.consumptions_list',
            compact('documents', 'search', 'warehouseId', 'issueType', 'status', 'dateFrom', 'dateTo'),
            'consumptions-report-' . date('Y-m-d') . '.pdf',
            ['landscape' => true]
        );
    }

    public function showPdf(WarehouseIssueDocument $consumption)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            abort(403, 'بخش انبار موجودی غیرفعال شده است.');
        }

        $consumption->load([
            'sourceWarehouse',
            'client',
            'consumerUser',
            'creator',
            'cancelledByUser',
            'items.item.category',
            'items.warehouse',
            'items.batchDeductions.batch',
        ]);

        return app(WarehousePdfService::class)->downloadInline(
            'warehouse::pdf.consumption_single',
            compact('consumption'),
            'issue-' . $consumption->issue_number . '.pdf'
        );
    }

    protected function buildFilteredQuery(
        string $search,
        $warehouseId,
        $issueType,
        $status,
        $dateFrom,
        $dateTo
    ) {
        $gregFrom = null;
        $gregTo = null;
        try {
            $gregFrom = $dateFrom ? $this->consumptionService->parseDate($dateFrom) : null;
            $gregTo = $dateTo ? $this->consumptionService->parseDate($dateTo) : null;
        } catch (\Throwable $e) {
            // Ignore partial/incomplete date filter
        }

        return WarehouseIssueDocument::with([
            'sourceWarehouse',
            'client',
            'consumerUser',
            'creator',
            'items.item',
            'items.warehouse',
        ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('issue_number', 'like', "%{$search}%")
                        ->orWhere('patient_name', 'like', "%{$search}%")
                        ->orWhere('patient_case_number', 'like', "%{$search}%")
                        ->orWhere('consumer_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($cq) use ($search) {
                            $cq->where('full_name', 'like', "%{$search}%")
                                ->orWhere('case_number', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items.item', function ($iq) use ($search) {
                            $iq->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('system_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where(function ($sub) use ($warehouseId) {
                    $sub->where('source_warehouse_id', $warehouseId)
                        ->orWhereHas('items', fn($i) => $i->where('warehouse_id', $warehouseId));
                });
            })
            ->when($issueType, fn($q) => $q->where('issue_type', $issueType))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($gregFrom, fn($q) => $q->whereDate('issue_date', '>=', $gregFrom))
            ->when($gregTo, fn($q) => $q->whereDate('issue_date', '<=', $gregTo));
    }

    protected function formatItemForAutocomplete(WarehouseItem $item, ?int $warehouseId = null): array
    {
        $batches = $item->activeBatches;
        $totalStock = (float)$batches->sum('remaining_quantity');
        $warehouseStock = $warehouseId
            ? (float)$batches->where('warehouse_id', $warehouseId)->sum('remaining_quantity')
            : $totalStock;

        $perWarehouse = [];
        foreach ($batches->groupBy('warehouse_id') as $wId => $grp) {
            $perWarehouse[(int)$wId] = (float)$grp->sum('remaining_quantity');
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code,
            'system_code' => $item->system_code,
            'unit' => $item->unit ?: 'عدد',
            'total_stock' => $totalStock,
            'warehouse_stock' => $warehouseStock,
            'per_warehouse' => $perWarehouse,
        ];
    }
}
