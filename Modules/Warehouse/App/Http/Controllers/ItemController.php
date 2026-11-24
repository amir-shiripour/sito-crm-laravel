<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseCategory;
use Modules\Warehouse\Entities\WarehouseItemBatch;
use Modules\Warehouse\App\Services\WarehouseCodeService;
use Modules\Warehouse\App\Services\WarehouseValuationService;
use Modules\Warehouse\App\Services\WarehouseExcelImportService;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    protected WarehouseCodeService $codeService;
    protected WarehouseValuationService $valuationService;
    protected WarehouseExcelImportService $importService;

    public function __construct(
        WarehouseCodeService $codeService,
        WarehouseValuationService $valuationService,
        WarehouseExcelImportService $importService
    ) {
        $this->codeService = $codeService;
        $this->valuationService = $valuationService;
        $this->importService = $importService;
    }

    /**
     * Display a listing of warehouse items with filters and metrics.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');
        $brand = $request->input('brand');
        $status = $request->input('status'); // all, critical, reorder, adequate
        $warehouseId = $request->input('warehouse_id');

        $query = WarehouseItem::with(['category', 'subcategory', 'activeBatches.warehouse'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('system_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('item_type', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($subcategoryId, fn($q) => $q->where('subcategory_id', $subcategoryId))
            ->when($brand, fn($q) => $q->where('brand', $brand))
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->whereHas('activeBatches', fn($b) => $b->where('warehouse_id', $warehouseId));
            });

        // Filter by stock status if requested
        if ($status === 'critical') {
            // where remaining <= min_stock
            $query->whereHas('activeBatches', function ($b) {
                // filter items below min stock
            });
        }

        $items = $query->latest('id')->paginate(15)->withQueryString();

        // Valuation and alerts metrics
        $metrics = $this->valuationService->getValuationMetrics($warehouseId ? (int)$warehouseId : null);

        $categories = WarehouseCategory::roots()->active()->with('children')->get();
        $warehouses = Warehouse::inventory()->active()->orderBy('is_main', 'desc')->orderBy('name')->get();
        $distinctBrands = WarehouseItem::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand');

        return view('warehouse::items.index', compact(
            'items',
            'metrics',
            'categories',
            'warehouses',
            'distinctBrands',
            'search',
            'categoryId',
            'subcategoryId',
            'brand',
            'status',
            'warehouseId'
        ));
    }

    /**
     * Show form to create a new item.
     */
    public function create()
    {
        $item = new WarehouseItem([
            'unit' => 'عدد',
            'is_active' => true,
            'min_stock' => 0,
            'reorder_point' => 0,
        ]);

        $systemCode = $this->codeService->generateSystemCode();
        $categories = WarehouseCategory::roots()->active()->with('children')->get();
        $warehouses = Warehouse::inventory()->active()->orderBy('is_main', 'desc')->orderBy('name')->get();
        $mainWarehouse = Warehouse::inventory()->main()->first();

        // Sample suggestions for comboboxes
        $suggestedBrands = $this->getPresetBrands();
        $suggestedTypes = $this->getPresetTypes();
        $suggestedSpecs = $this->getPresetSpecs();
        $suggestedUnits = ['عدد', 'بسته', 'جعبه', 'ست', 'ویال', 'سرنگ', 'تیوب', 'کیلوگرم', 'لیتر', 'متر'];

        return view('warehouse::items.create', compact(
            'item',
            'systemCode',
            'categories',
            'warehouses',
            'mainWarehouse',
            'suggestedBrands',
            'suggestedTypes',
            'suggestedSpecs',
            'suggestedUnits'
        ));
    }

    /**
     * Store newly created item.
     */
    public function store(Request $request)
    {
        $cleanFields = ['current_price', 'last_purchase_price', 'opening_purchase_price', 'min_stock', 'reorder_point', 'opening_stock'];
        foreach ($cleanFields as $field) {
            if ($request->has($field) && !is_null($request->input($field))) {
                $cleaned = str_replace([',', ' '], '', (string)$request->input($field));
                $cleaned = strtr($cleaned, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                $request->merge([$field => $cleaned !== '' ? $cleaned : null]);
            }
        }

        $validated = $request->validate([
            'system_code' => 'required|string|max:60|unique:warehouse_items,system_code',
            'code' => 'required|string|max:100|unique:warehouse_items,code',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:warehouse_categories,id',
            'subcategory_id' => 'nullable|exists:warehouse_categories,id',
            'brand' => 'nullable|string|max:100',
            'brand_code' => 'nullable|string|max:50',
            'item_type' => 'nullable|string|max:100',
            'item_type_code' => 'nullable|string|max:50',
            'model_spec' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'current_price' => 'nullable|numeric|min:0',
            'last_purchase_price' => 'nullable|numeric|min:0',
            'default_warehouse_id' => 'nullable|exists:warehouse_warehouses,id',
            'default_location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'has_expiry' => 'sometimes|boolean',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',

            // Opening stock fields
            'opening_stock' => 'nullable|integer|min:0',
            'opening_purchase_price' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouse_warehouses,id',
        ], [
            'name.required' => 'نام کالا الزامی است.',
            'code.required' => 'کد تجاری / SKU کالا الزامی است.',
            'code.unique' => 'این کد کالا قبلاً استفاده شده است.',
            'system_code.required' => 'کد سیستمی کالا الزامی است.',
            'system_code.unique' => 'این کد سیستمی قبلاً استفاده شده است.',
            'unit.required' => 'واحد سنجش الزامی است.',
        ]);

        $openingStock = (float)($request->input('opening_stock') ?: 0);
        $openingPrice = (float)($request->input('opening_purchase_price') ?: 0);
        $warehouseId = $request->input('warehouse_id');

        unset($validated['opening_stock'], $validated['opening_purchase_price'], $validated['warehouse_id']);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['has_expiry'] = $request->boolean('has_expiry', false);

        if ($openingPrice > 0 && empty($validated['last_purchase_price'])) {
            $validated['last_purchase_price'] = $openingPrice;
        }

        $item = DB::transaction(function () use ($validated, $openingStock, $openingPrice, $warehouseId) {
            $item = WarehouseItem::create($validated);

            if ($openingStock > 0) {
                $targetWarehouseId = $warehouseId ?: Warehouse::inventory()->main()->value('id');
                if ($targetWarehouseId) {
                    $this->valuationService->addBatch(
                        $item,
                        (int)$targetWarehouseId,
                        $openingStock,
                        $openingPrice,
                        'OPENING-' . $item->system_code,
                        now()->toDateString(),
                        null,
                        'موجودی اول دوره',
                        'ثبت افتتاحیه در زمان تعریف اولیه کالا'
                    );
                }
            }

            return $item;
        });

        return redirect()->route('admin.warehouse.items.index')
            ->with('success', "کالای «{$item->name}» با موفقیت تعریف شد.");
    }

    /**
     * Display item details with stock breakdown per warehouse and batch valuation.
     */
    public function show(WarehouseItem $item)
    {
        $item->load(['category', 'subcategory', 'stocks.warehouse', 'batches.warehouse', 'defaultWarehouse']);

        return view('warehouse::items.show', compact('item'));
    }

    /**
     * Show form to edit an existing item.
     */
    public function edit(WarehouseItem $item)
    {
        $categories = WarehouseCategory::roots()->active()->with('children')->get();
        $warehouses = Warehouse::inventory()->active()->orderBy('is_main', 'desc')->orderBy('name')->get();

        $suggestedBrands = $this->getPresetBrands();
        $suggestedTypes = $this->getPresetTypes();
        $suggestedSpecs = $this->getPresetSpecs();
        $suggestedUnits = ['عدد', 'بسته', 'جعبه', 'ست', 'ویال', 'سرنگ', 'تیوب', 'کیلوگرم', 'لیتر', 'متر'];

        return view('warehouse::items.edit', compact(
            'item',
            'categories',
            'warehouses',
            'suggestedBrands',
            'suggestedTypes',
            'suggestedSpecs',
            'suggestedUnits'
        ));
    }

    /**
     * Update an item.
     */
    public function update(Request $request, WarehouseItem $item)
    {
        $cleanFields = ['current_price', 'last_purchase_price', 'min_stock', 'reorder_point'];
        foreach ($cleanFields as $field) {
            if ($request->has($field) && !is_null($request->input($field))) {
                $cleaned = str_replace([',', ' '], '', (string)$request->input($field));
                $cleaned = strtr($cleaned, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                $request->merge([$field => $cleaned !== '' ? $cleaned : null]);
            }
        }

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:warehouse_items,code,' . $item->id,
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:warehouse_categories,id',
            'subcategory_id' => 'nullable|exists:warehouse_categories,id',
            'brand' => 'nullable|string|max:100',
            'brand_code' => 'nullable|string|max:50',
            'item_type' => 'nullable|string|max:100',
            'item_type_code' => 'nullable|string|max:50',
            'model_spec' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'current_price' => 'nullable|numeric|min:0',
            'last_purchase_price' => 'nullable|numeric|min:0',
            'default_warehouse_id' => 'nullable|exists:warehouse_warehouses,id',
            'default_location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'has_expiry' => 'sometimes|boolean',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ], [
            'name.required' => 'نام کالا الزامی است.',
            'code.required' => 'کد تجاری / SKU کالا الزامی است.',
            'code.unique' => 'این کد کالا قبلاً برای کالای دیگری استفاده شده است.',
            'unit.required' => 'واحد سنجش الزامی است.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['has_expiry'] = $request->boolean('has_expiry', false);

        $item->update($validated);

        return redirect()->route('admin.warehouse.items.show', $item)
            ->with('success', "اطلاعات کالای «{$item->name}» با موفقیت به‌روزرسانی شد.");
    }

    /**
     * Delete an item (soft delete).
     */
    public function destroy(WarehouseItem $item)
    {
        $name = $item->name;
        $item->delete();

        return redirect()->route('admin.warehouse.items.index')
            ->with('success', "کالای «{$name}» با موفقیت حذف شد.");
    }

    /**
     * AJAX helper to suggest code and name dynamically based on chosen attributes.
     */
    public function suggestCode(Request $request)
    {
        $catId = $request->input('category_id');
        $subcatId = $request->input('subcategory_id');
        $itemType = $request->input('item_type');
        $itemTypeCode = $request->input('item_type_code');
        $brand = $request->input('brand');
        $brandCode = $request->input('brand_code');
        $spec = $request->input('spec');

        $catCode = $catId ? WarehouseCategory::where('id', $catId)->value('code') : null;
        $subcat = $subcatId ? WarehouseCategory::where('id', $subcatId)->first() : null;
        $subcatCode = $subcat?->code;
        $subcatName = $subcat?->name ?: ($catId ? WarehouseCategory::where('id', $catId)->value('name') : null);

        $suggestedSku = $this->codeService->generateSuggestedSku($catCode, $subcatCode, $itemTypeCode, $brandCode, $spec);
        $suggestedName = $this->codeService->generateSuggestedName($subcatName, $itemType, $brand, $spec);

        return response()->json([
            'suggested_sku' => $suggestedSku,
            'suggested_name' => $suggestedName,
        ]);
    }

    /**
     * Display import view.
     */
    public function importForm()
    {
        $presetPath = base_path('tests/سیستم_انبار_کلینیک_دکتر_ذبیحی_اصلی_1.xlsx');
        $presetExists = file_exists($presetPath);

        return view('warehouse::items.import', compact('presetPath', 'presetExists'));
    }

    /**
     * Process Excel import submission.
     */
    public function importSubmit(Request $request)
    {
        $usePreset = $request->boolean('use_preset');
        $presetPath = base_path('tests/سیستم_انبار_کلینیک_دکتر_ذبیحی_اصلی_1.xlsx');

        if ($usePreset) {
            if (!file_exists($presetPath)) {
                return back()->with('error', 'فایل پیش‌فرض کلینیک یافت نشد.');
            }
            $targetPath = $presetPath;
        } else {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            ], [
                'excel_file.required' => 'لطفاً یک فایل اکسل انتخاب کنید.',
                'excel_file.mimes' => 'فرمت فایل انتخابی باید xlsx یا xls باشد.',
            ]);

            $file = $request->file('excel_file');
            $targetPath = $file->getRealPath();
        }

        try {
            $result = $this->importService->import($targetPath);

            if (!empty($result['errors'])) {
                return back()->with('error', implode(' | ', $result['errors']));
            }

            $msg = "درون‌ریزی با موفقیت انجام شد: {$result['imported_items']} کالای جدید ثبت شد، {$result['updated_items']} کالا به‌روزرسانی شد و {$result['opening_batches_created']} سند افتتاحیه صادر گردید.";

            return redirect()->route('admin.warehouse.items.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در درون‌ریزی فایل اکسل: ' . $e->getMessage());
        }
    }

    protected function getPresetBrands(): array
    {
        return [
            'بگو(BEGO)',
            'بردنت(BREDENT)',
            'بایوتم(BIOTEM)',
            'چاروم(CIS)',
            'لونا(LUNA)',
            'روت(ROOT)',
            'اشترومن(STRUMAN)',
            'SIC',
            'اسنو(SNOW)',
            'دیو(DIO)',
            'مگاژن(MEGAGEN)',
            'دنتیوم(DENTIUM)',
            'استرومن(STRAUMANN)',
            'نوبل بایوکر(NOBEL BIOCARE)',
            'استرا(ASTRA)',
            'زیمر(ZIMMER)',
        ];
    }

    protected function getPresetTypes(): array
    {
        return [
            'RS PRO',
            'SKY',
            'COPA',
            'R',
            'P',
            'M',
            'C',
            'SLA',
            'Narrow',
            'Regular',
            'Wide',
            'Internal Hex',
            'Conical',
            'Submerged',
        ];
    }

    protected function getPresetSpecs(): array
    {
        return [
            '3.75 x 8.5',
            '3.75 x 10',
            '3.75 x 11.5',
            '3.75 x 13',
            '4.1 x 7',
            '4.1 x 8.5',
            '4.1 x 10',
            '4.1 x 11.5',
            '4.5 x 8.5',
            '4.5 x 10',
            '4.5 x 11.5',
            '5.0 x 8.5',
            '5.0 x 10',
        ];
    }
}
