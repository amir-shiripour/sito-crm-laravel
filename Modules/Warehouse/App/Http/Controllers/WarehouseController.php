<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseItemBatch;
use Modules\Warehouse\Entities\WarehouseSetting;
use Modules\Warehouse\App\Services\WarehouseIntegrationService;
use Modules\Warehouse\App\Services\WarehousePdfService;
use Modules\Warehouse\App\Services\WarehouseValuationService;
use Modules\Warehouse\Support\WarehouseUserVisibility;
use App\Models\User;

class WarehouseController extends Controller
{
    protected WarehouseIntegrationService $integrationService;

    public function __construct(WarehouseIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Search users dynamically for warehouse managers.
     * Note: Does not auto-load users if query is empty ("خوکار کاربرا لود نشن").
     */
    public function searchUsers(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $ids = $request->input('ids');

        if (empty($q) && empty($ids)) {
            return response()->json([
                'results' => [],
                'total' => 0,
            ]);
        }

        $query = User::query();
        WarehouseUserVisibility::scopeVisibleUsers($query);

        if (!empty($ids)) {
            $idsArray = is_array($ids) ? $ids : array_filter(array_map('intval', explode(',', (string)$ids)));
            if (!empty($idsArray)) {
                $query->whereIn('id', $idsArray);
            }
        } elseif (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        $users = $query->select('id', 'name', 'email', 'mobile', 'profile_photo_path')
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email ?? '',
                    'mobile' => $u->mobile ?? '',
                    'initial' => mb_substr(trim($u->name ?: 'ک'), 0, 1, 'utf-8'),
                    'avatar' => method_exists($u, 'getProfilePhotoUrlAttribute') ? $u->profile_photo_url : null,
                ];
            });

        return response()->json([
            'results' => $users,
            'total' => $users->count(),
        ]);
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
            ->with(['managers', 'creator'])
            ->first();

        // If no explicit main warehouse, find first root warehouse
        if (!$mainWarehouse) {
            $mainWarehouse = Warehouse::inventory()
                ->whereNull('parent_id')
                ->with(['managers', 'creator'])
                ->first();
        }

        $subWarehousesQuery = Warehouse::inventory()
            ->when($mainWarehouse, fn($q) => $q->where('id', '!=', $mainWarehouse->id))
            ->with(['managers', 'creator'])
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

        $allVisibleWhIds = $subWarehouses->pluck('id')->when($mainWarehouse, fn($c) => $c->push($mainWarehouse->id));
        $stockSummaries = app(WarehouseValuationService::class)->getWarehousesStockSummary($allVisibleWhIds);

        return view('warehouse::inventory.index', compact(
            'mainWarehouse',
            'subWarehouses',
            'stockSummaries',
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
            ->with(['managers', 'creator'])
            ->first();

        // If no explicit main warehouse, find first root warehouse
        if (!$mainWarehouse) {
            $mainWarehouse = Warehouse::asset()
                ->whereNull('parent_id')
                ->with(['managers', 'creator'])
                ->first();
        }

        $subWarehousesQuery = Warehouse::asset()
            ->when($mainWarehouse, fn($q) => $q->where('id', '!=', $mainWarehouse->id))
            ->with(['managers', 'creator'])
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
     * Export Inventory Warehouses report as official PDF.
     */
    public function inventoryPdf(Request $request)
    {
        if (!WarehouseSetting::isInventoryEnabled()) {
            abort(403, 'بخش انبار موجودی غیرفعال است.');
        }

        return $this->renderWarehousesPdf('inventory', $request->input('search'));
    }

    /**
     * Export Asset Warehouses report as official PDF.
     */
    public function assetsPdf(Request $request)
    {
        if (!WarehouseSetting::isAssetsEnabled()) {
            abort(403, 'بخش مدیریت اموال غیرفعال است.');
        }

        return $this->renderWarehousesPdf('asset', $request->input('search'));
    }

    protected function renderWarehousesPdf(string $type, ?string $search = null)
    {
        $warehouses = Warehouse::where('type', $type)
            ->with(['managers', 'creator'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('manager', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $mainWarehouse = $warehouses->firstWhere('is_main', true) ?? $warehouses->firstWhere('parent_id', null);

        $stockTotals = WarehouseItemBatch::whereIn('warehouse_id', $warehouses->pluck('id'))
            ->where('remaining_quantity', '>', 0)
            ->selectRaw('warehouse_id, SUM(remaining_quantity) as total_qty')
            ->groupBy('warehouse_id')
            ->pluck('total_qty', 'warehouse_id')
            ->toArray();

        $filename = 'warehouse-' . $type . '-report-' . date('Y-m-d') . '.pdf';

        return app(WarehousePdfService::class)->downloadInline('warehouse::pdf.warehouses_list', compact(
            'type',
            'warehouses',
            'mainWarehouse',
            'stockTotals'
        ), $filename);
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

        $currentManagers = [];

        return view('warehouse::warehouses.create', compact('warehouse', 'type', 'parentWarehouses', 'currentManagers'));
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
            'manager_ids' => 'nullable|array',
            'manager_ids.*' => 'exists:users,id',
            'manager' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ], [
            'name.required' => 'نام انبار الزامی است.',
            'code.unique' => 'این کد انبار قبلاً استفاده شده است.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $hasMain = Warehouse::where('type', $validated['type'])->where('is_main', true)->exists();
        $validated['is_main'] = (!$hasMain && empty($validated['parent_id']));

        $managerIds = WarehouseUserVisibility::sanitizeSelectableUserIds((array)$request->input('manager_ids', []));
        unset($validated['manager_ids']);

        $warehouse = Warehouse::create($validated);

        if (count($managerIds) > 0) {
            $warehouse->managers()->sync($managerIds);
            $names = $warehouse->managers()->pluck('name')->implode('، ');
            $warehouse->updateQuietly(['manager' => $names]);
        }

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

        $currentManagers = WarehouseUserVisibility::filterVisibleManagers($warehouse->managers()->get())
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email ?? '',
                    'mobile' => $u->mobile ?? '',
                    'initial' => mb_substr(trim($u->name ?: 'ک'), 0, 1, 'utf-8'),
                    'avatar' => method_exists($u, 'getProfilePhotoUrlAttribute') ? $u->profile_photo_url : null,
                ];
            })->values()->toArray();

        return view('warehouse::warehouses.edit', compact('warehouse', 'parentWarehouses', 'currentManagers'));
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
            'manager_ids' => 'nullable|array',
            'manager_ids.*' => 'exists:users,id',
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

        $managerIds = $request->input('manager_ids', []);
        unset($validated['manager_ids']);

        $warehouse->update($validated);

        if ($request->has('manager_ids')) {
            $sanitizedIds = WarehouseUserVisibility::sanitizeSelectableUserIds(is_array($managerIds) ? $managerIds : []);
            if (!WarehouseUserVisibility::isSuperAdmin()) {
                $existingSuperAdminIds = $warehouse->managers()
                    ->whereHas('roles', fn($r) => $r->where('name', 'super-admin'))
                    ->pluck('users.id')
                    ->map(fn($id) => (int)$id)
                    ->all();
                $sanitizedIds = array_values(array_unique(array_merge($sanitizedIds, $existingSuperAdminIds)));
            }
            $warehouse->managers()->sync($sanitizedIds);
            $names = $warehouse->managers()->pluck('name')->implode('، ');
            $warehouse->updateQuietly(['manager' => $names]);
        }

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
        // Prevent deletion if items are stocked in this warehouse
        if (\Modules\Warehouse\Entities\WarehouseItemStock::where('warehouse_id', $warehouse->id)->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'این انبار دارای موجودی کالا است و نمی‌توان آن را حذف کرد.');
        }

        // If it has children, re-parent them to main warehouse or null
        if ($warehouse->children()->exists()) {
            $mainWarehouse = Warehouse::where('type', $warehouse->type)
                ->where('id', '!=', $warehouse->id)
                ->main()
                ->first();

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
