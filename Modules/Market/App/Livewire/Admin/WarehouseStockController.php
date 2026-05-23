<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\Warehouse;
use Modules\Market\Entities\WarehouseStock;
use Modules\Market\Entities\WarehouseTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class WarehouseStockController extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $stockStatusFilter = 'all';

    public $editingVariantId = null;
    public $editableStocks = [];

    protected function rules()
    {
        return [
            'editableStocks.*.physical_stock' => 'required|integer|min:0',
            'editableStocks.*.online_stock' => 'required|integer|min:0',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setStockStatusFilter($status)
    {
        $this->stockStatusFilter = $status;
        $this->resetPage();
    }

    #[Computed]
    public function relevantWarehouses()
    {
        $query = Warehouse::where('is_active', true);

        if (Auth::user()->hasRole('vendor')) {
            $query->where('vendor_id', Auth::user()->marketVendor->id);
        }

        return $query->get();
    }

    #[Computed]
    public function products()
    {
        $relevantWarehouseIds = $this->relevantWarehouses()->pluck('id');
        $isVendor = Auth::user()->hasRole('vendor');
        $vendorId = $isVendor ? Auth::user()->marketVendor->id : null;

        $wmsSubQuery = WarehouseStock::query()
            ->select(
                'product_variant_id',
                DB::raw('SUM(physical_stock) as total_physical_stock'),
                DB::raw('SUM(online_stock) as total_online_stock'),
                DB::raw('SUM(reserved_stock) as total_reserved_stock')
            )
            ->whereIn('warehouse_id', $relevantWarehouseIds)
            ->groupBy('product_variant_id');

        $vpSubQuery = VendorProduct::query()
            ->select(
                'product_variant_id',
                DB::raw('MAX(id) as vendor_product_id'),
                DB::raw('SUM(stock) as legacy_stock')
            )
            ->when($isVendor, function ($q) use ($vendorId) {
                // If vendor, strict match.
                $q->where('vendor_id', $vendorId);
            }, function ($q) {
                // If admin, we match system products (null vendor_id).
                // Or you could allow admins to see all, but here we focus on the specific 'vendor_id' scope.
                $q->whereNull('vendor_id');
            })
            ->groupBy('product_variant_id');

        $query = ProductVariant::query()
            ->leftJoinSub($wmsSubQuery, 'wms_summary', function ($join) {
                $join->on('market_product_variants.id', '=', 'wms_summary.product_variant_id');
            })
            ->leftJoinSub($vpSubQuery, 'vp_summary', function ($join) {
                $join->on('market_product_variants.id', '=', 'vp_summary.product_variant_id');
            });

        if ($isVendor) {
            $query->whereNotNull('vp_summary.vendor_product_id');
        }

        $query->select(
            'market_product_variants.*',
            'vp_summary.vendor_product_id',
            'vp_summary.legacy_stock',
            DB::raw('COALESCE(wms_summary.total_physical_stock, 0) as total_physical_stock'),
            DB::raw('COALESCE(wms_summary.total_online_stock, 0) as total_online_stock'),
            DB::raw('COALESCE(wms_summary.total_reserved_stock, 0) as total_reserved_stock')
        );

        $query->with(['masterProduct']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('masterProduct', function ($q2) {
                    $q2->where('title', 'like', '%' . $this->search . '%');
                })->orWhere('variant_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->stockStatusFilter === 'in_stock') {
            $query->havingRaw('COALESCE(total_physical_stock, 0) + COALESCE(total_online_stock, 0) > 0');
        } elseif ($this->stockStatusFilter === 'out_of_stock') {
            $query->havingRaw('(COALESCE(total_physical_stock, 0) + COALESCE(total_online_stock, 0)) = 0');
        }

        return $query->paginate(10);
    }


    public function toggleEditStock($variantId)
    {
        if ($this->editingVariantId === $variantId) {
            $this->cancelEdit();
            return;
        }

        $this->editingVariantId = $variantId;
        $this->editableStocks = [];

        // Get all relevant warehouse IDs for the current user context.
        $relevantWarehouses = $this->relevantWarehouses();
        $relevantWarehouseIds = $relevantWarehouses->pluck('id');

        // Fetch all existing stock records for this variant in the relevant warehouses in a single query.
        $existingStocks = WarehouseStock::where('product_variant_id', $variantId)
            ->whereIn('warehouse_id', $relevantWarehouseIds)
            ->get()
            ->keyBy('warehouse_id'); // Key by warehouse_id for efficient lookup.

        // Iterate through the relevant warehouses to build the form structure,
        // ensuring all warehouses appear in the form.
        foreach ($relevantWarehouses as $warehouse) {
            // Find the specific stock record from our collection.
            $stock = $existingStocks->get($warehouse->id);

            // Populate the form data. If a stock record exists, use its value; otherwise, default to 0.
            $this->editableStocks[$warehouse->id] = [
                'physical_stock' => $stock->physical_stock ?? 0,
                'online_stock' => $stock->online_stock ?? 0,
            ];
        }
    }

    public function cancelEdit()
    {
        $this->editingVariantId = null;
        $this->editableStocks = [];
        $this->resetValidation();
    }

    public function saveStocksForVariant($variantId)
    {
        $this->validate();

        $variant = ProductVariant::findOrFail($variantId);
        $userId = Auth::id();
        $isVendor = Auth::user()->hasRole('vendor');
        $vendorId = $isVendor ? Auth::user()->marketVendor->id : null;

        DB::beginTransaction();
        try {
            // Guarantee a VendorProduct entry exists for this context
            $vendorProduct = VendorProduct::firstOrCreate(
                ['vendor_id' => $vendorId, 'product_variant_id' => $variant->id],
                ['status' => 'draft', 'price' => 0] // Defaults if freshly created
            );

            // 1. Process and save individual warehouse stock updates
            foreach ($this->editableStocks as $warehouseId => $data) {
                // Security check: ensure the warehouse belongs to the context
                $warehouseQuery = Warehouse::where('id', $warehouseId);
                if ($isVendor) {
                    $warehouseQuery->where('vendor_id', $vendorId);
                } else {
                    $warehouseQuery->whereNull('vendor_id');
                }

                $warehouse = $warehouseQuery->first();
                if (!$warehouse) {
                    continue; // Skip if invalid permission
                }

                $currentStock = WarehouseStock::firstOrNew([
                    'warehouse_id' => $warehouseId,
                    'product_variant_id' => $variant->id,
                    'vendor_product_id' => $vendorProduct->id,
                ]);

                $oldPhysical = $currentStock->physical_stock ?? 0;
                $oldOnline = $currentStock->online_stock ?? 0;

                $newPhysical = (int) $data['physical_stock'];
                $newOnline = (int) $data['online_stock'];

                // Do not create empty stock records if they don't exist
                if (!$currentStock->exists && $newPhysical === 0 && $newOnline === 0) {
                    continue;
                }

                $currentStock->physical_stock = $newPhysical;
                $currentStock->online_stock = $newOnline;
                $currentStock->save();

                // Log physical transactions
                if ($oldPhysical !== $newPhysical) {
                    WarehouseTransaction::create([
                        'warehouse_id' => $warehouseId,
                        'product_variant_id' => $variant->id,
                        'vendor_product_id' => $vendorProduct->id,
                        'type' => 'adjustment',
                        'quantity' => $newPhysical - $oldPhysical,
                        'description' => 'تعدیل موجودی فیزیکی',
                        'user_id' => $userId,
                    ]);
                }

                // Log online transactions
                if ($oldOnline !== $newOnline) {
                    WarehouseTransaction::create([
                        'warehouse_id' => $warehouseId,
                        'product_variant_id' => $variant->id,
                        'vendor_product_id' => $vendorProduct->id,
                        'type' => 'adjustment',
                        'quantity' => $newOnline - $oldOnline,
                        'description' => 'تعدیل موجودی آنلاین',
                        'user_id' => $userId,
                    ]);
                }
            }

            // 2. Strict Two-Way Synchronization back to Legacy System (Source of Truth for frontend)
            // Recalculate the absolute sum of online_stock for ALL active warehouses belonging to this vendor/system.
            $warehouseQueryForSync = Warehouse::where('is_active', true);
            if ($isVendor) {
                $warehouseQueryForSync->where('vendor_id', $vendorId);
            } else {
                $warehouseQueryForSync->whereNull('vendor_id');
            }
            $activeWarehouseIds = $warehouseQueryForSync->pluck('id');

            $totalOnlineStock = WarehouseStock::where('product_variant_id', $variant->id)
                ->whereIn('warehouse_id', $activeWarehouseIds)
                ->sum('online_stock');

            // Forcefully update the legacy stock field so the rest of the CRM behaves accurately
            $vendorProduct->update(['stock' => $totalOnlineStock]);

            DB::commit();

            $this->dispatch('notify', type: 'success', text: 'موجودی انبار با موفقیت بروزرسانی شد و با سیستم هماهنگ گردید.');
            $this->cancelEdit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', text: 'خطایی در بروزرسانی موجودی رخ داد: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('market::livewire.admin.warehouse-stock-controller');
    }
}
