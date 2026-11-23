<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseSetting;
use Modules\Warehouse\App\Services\WarehouseIntegrationService;

class WarehouseController extends Controller
{
    protected WarehouseIntegrationService $integrationService;

    public function __construct(WarehouseIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * List all Inventory warehouses and their sub-warehouses.
     */
    public function inventoryIndex(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی در تنظیمات غیرفعال شده است.');
        }

        $search = $request->input('search');

        $mainWarehouse = Warehouse::inventory()
            ->main()
            ->first();

        // If for some reason main warehouse doesn't exist yet, create one
        if (!$mainWarehouse) {
            $mainWarehouse = Warehouse::create([
                'name' => 'انبار مرکزی موجودی',
                'type' => 'inventory',
                'is_main' => true,
                'is_active' => true,
                'color' => '#4f46e5',
                'location' => 'دفتر مرکزی',
            ]);
        }

        $subWarehousesQuery = Warehouse::inventory()
            ->where('is_main', false)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('manager', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            });

        $subWarehouses = $subWarehousesQuery->latest()->paginate(12)->withQueryString();

        // Linked Market warehouses
        $linkedMarketWarehouses = collect();
        $isMarketIntegrated = WarehouseSetting::isMarketIntegrationEnabled() && $this->integrationService->isMarketWmsEnabled();
        if ($isMarketIntegrated && $mainWarehouse) {
            $linkedMarketWarehouses = $this->integrationService->getLinkedMarketWarehouses($mainWarehouse->id);
        }

        return view('warehouse::inventory.index', compact(
            'mainWarehouse',
            'subWarehouses',
            'linkedMarketWarehouses',
            'isMarketIntegrated',
            'search'
        ));
    }

    /**
     * List all Asset warehouses and their sub-warehouses.
     */
    public function assetsIndex(Request $request)
    {
        if (!WarehouseSetting::isAssetsEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش مدیریت اموال در تنظیمات غیرفعال شده است.');
        }

        $search = $request->input('search');

        $mainWarehouse = Warehouse::asset()
            ->main()
            ->first();

        // If for some reason main warehouse doesn't exist yet, create one
        if (!$mainWarehouse) {
            $mainWarehouse = Warehouse::create([
                'name' => 'انبار مرکزی اموال',
                'type' => 'asset',
                'is_main' => true,
                'is_active' => true,
                'color' => '#0d9488',
                'location' => 'دفتر مرکزی',
            ]);
        }

        $subWarehousesQuery = Warehouse::asset()
            ->where('is_main', false)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('manager', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            });

        $subWarehouses = $subWarehousesQuery->latest()->paginate(12)->withQueryString();

        return view('warehouse::assets.index', compact(
            'mainWarehouse',
            'subWarehouses',
            'search'
        ));
    }

    /**
     * Show form to create a new warehouse or sub-warehouse.
     */
    public function create(Request $request)
    {
        $type = $request->query('type', 'inventory');
        if (!in_array($type, ['inventory', 'asset'])) {
            $type = 'inventory';
        }

        $parentId = $request->query('parent_id');

        // Check if the type is enabled
        if ($type === 'inventory' && !WarehouseSetting::isInventoryEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش انبار موجودی غیرفعال است.');
        }
        if ($type === 'asset' && !WarehouseSetting::isAssetsEnabled()) {
            return redirect()->route('admin.warehouse.dashboard')
                ->with('error', 'بخش مدیریت اموال غیرفعال است.');
        }

        // Possible parent warehouses of this type
        $parentWarehouses = Warehouse::where('type', $type)
            ->where('is_active', true)
            ->get();

        $warehouse = new Warehouse([
            'type' => $type,
            'parent_id' => $parentId,
            'color' => $type === 'inventory' ? '#4f46e5' : '#0d9488',
            'is_active' => true,
        ]);

        return view('warehouse::warehouses.create', compact('warehouse', 'type', 'parentWarehouses'));
    }

    /**
     * Store newly created warehouse.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:inventory,asset',
            'parent_id' => 'nullable|exists:warehouse_warehouses,id',
            'color' => 'nullable|string|max:10',
            'code' => 'nullable|string|max:50|unique:warehouse_warehouses,code',
            'location' => 'nullable|string|max:255',
            'manager' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ], [
            'name.required' => 'نام انبار الزامی است.',
            'code.unique' => 'این کد انبار قبلاً استفاده شده است.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_main'] = false; // User-created ones are sub-warehouses

        $warehouse = Warehouse::create($validated);

        $route = $warehouse->type === 'inventory'
            ? 'admin.warehouse.inventory.index'
            : 'admin.warehouse.assets.index';

        return redirect()->route($route)
            ->with('success', "انبار «{$warehouse->name}» با موفقیت ایجاد شد.");
    }

    /**
     * Show form to edit a warehouse.
     */
    public function edit(Warehouse $warehouse)
    {
        $parentWarehouses = Warehouse::where('type', $warehouse->type)
            ->where('id', '!=', $warehouse->id)
            ->where(function ($q) use ($warehouse) {
                // exclude direct children to prevent cyclic recursion
                $q->where('parent_id', '!=', $warehouse->id)->orWhereNull('parent_id');
            })
            ->get();

        return view('warehouse::warehouses.edit', compact('warehouse', 'parentWarehouses'));
    }

    /**
     * Update warehouse.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:10',
            'code' => 'nullable|string|max:50|unique:warehouse_warehouses,code,' . $warehouse->id,
            'location' => 'nullable|string|max:255',
            'manager' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ];

        if (!$warehouse->is_main) {
            $rules['parent_id'] = 'nullable|exists:warehouse_warehouses,id|different:id';
        }

        $validated = $request->validate($rules, [
            'name.required' => 'نام انبار الزامی است.',
            'code.unique' => 'این کد انبار قبلاً استفاده شده است.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Do not allow changing type or is_main
        unset($validated['type']);
        unset($validated['is_main']);

        $warehouse->update($validated);

        $route = $warehouse->type === 'inventory'
            ? 'admin.warehouse.inventory.index'
            : 'admin.warehouse.assets.index';

        return redirect()->route($route)
            ->with('success', "انبار «{$warehouse->name}» با موفقیت به‌روزرسانی شد.");
    }

    /**
     * Delete warehouse.
     */
    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->is_main) {
            return back()->with('error', 'انبار مرکزی قابل حذف نیست.');
        }

        // If it has children, re-parent them to main warehouse or null
        if ($warehouse->children()->exists()) {
            $mainWarehouse = Warehouse::where('type', $warehouse->type)->main()->first();
            $warehouse->children()->update([
                'parent_id' => $mainWarehouse ? $mainWarehouse->id : null,
            ]);
        }

        $name = $warehouse->name;
        $type = $warehouse->type;
        $warehouse->delete();

        $route = $type === 'inventory'
            ? 'admin.warehouse.inventory.index'
            : 'admin.warehouse.assets.index';

        return redirect()->route($route)
            ->with('success', "انبار «{$name}» با موفقیت حذف شد.");
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Warehouse $warehouse)
    {
        $warehouse->is_active = !$warehouse->is_active;
        $warehouse->save();

        $statusText = $warehouse->is_active ? 'فعال' : 'غیرفعال';
        return back()->with('success', "وضعیت انبار «{$warehouse->name}» به {$statusText} تغییر یافت.");
    }
}
