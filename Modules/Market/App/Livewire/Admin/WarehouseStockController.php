<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\Brand;
use Modules\Market\Entities\Category;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\Warehouse;
use Modules\Market\Entities\WarehouseStock;
use Modules\Market\Entities\WarehouseTransaction;
use Modules\Market\Entities\WarehouseTransfer;
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
    public $stockStatusFilter = 'all'; // 'all', 'in_stock', 'out_of_stock', 'low_stock', 'has_reserved', 'discrepancy'

    #[Url]
    public $warehouseFilter = 'central'; // 'central', 'vendors', 'all'

    #[Url]
    public $selectedVendorId = '';

    #[Url]
    public $selectedWarehouseId = '';

    #[Url]
    public $brandFilter = '';

    #[Url]
    public $categoryFilter = '';

    #[Url]
    public $sortBy = 'latest'; // 'latest', 'physical_desc', 'physical_asc', 'online_desc', 'reserved_desc', 'title_asc'

    #[Url]
    public $currentTab = 'stock'; // 'warehouses', 'stock', 'transactions', 'transfers'

    public $editingVariantId = null;
    public $editableStocks = [];

    // Properties for transactions listing tab
    #[Url]
    public $txFilterWarehouseId = '';

    #[Url]
    public $txFilterType = 'all';

    #[Url]
    public $txSearch = '';

    // Properties for transfers listing tab
    #[Url]
    public $tfFilterStatus = 'all';

    #[Url]
    public $tfSearch = '';

    // Properties for manual WMS voucher form
    public $showTxVariantId = null;
    public $txWarehouseId = '';
    public $txType = 'in'; // 'in' or 'out'
    public $txStockType = 'both'; // 'physical', 'online', 'both'
    public $txQuantity = '';
    public $txUnitPrice = ''; // Purchase Price for 'in', Selling Price for 'out'
    public $txReason = '';
    public $txCustomDescription = '';
    public $txDocumentRef = '';

    // Properties for Inter-Warehouse Transfers
    public $showTransferVariantId = null;
    public $tfSourceWarehouseId = '';
    public $tfDestinationWarehouseId = '';
    public $tfQuantity = '';

    public $showRejectTransferId = null;
    public $rejectReason = '';

    public function mount()
    {
        $isVendorRoute = request()->routeIs('user.market.vendor.*') || request()->is('user/market/vendor/*');

        if (!$this->isAdminUser() || ($isVendorRoute && !request()->has('warehouseFilter'))) {
            $this->warehouseFilter = 'vendors';
        }

        if ($this->selectedWarehouseId) {
            $wh = Warehouse::find($this->selectedWarehouseId);
            if ($wh) {
                if ($this->isAdminUser()) {
                    if ($wh->vendor_id && $this->warehouseFilter === 'central') {
                        $this->warehouseFilter = 'vendors';
                    } elseif (!$wh->vendor_id && $this->warehouseFilter === 'vendors') {
                        $this->warehouseFilter = 'central';
                    }
                    if ($wh->vendor_id && empty($this->selectedVendorId)) {
                        $this->selectedVendorId = (string) $wh->vendor_id;
                    }
                } else {
                    // Ensure non-admin vendor only selects their own warehouse
                    $myVendorId = Auth::user()?->marketVendor?->id;
                    if ($wh->vendor_id !== $myVendorId) {
                        $this->selectedWarehouseId = '';
                    }
                }
            }
        } elseif ($isVendorRoute && $this->isAdminUser() && empty($this->selectedVendorId)) {
            $myVendorId = Auth::user()?->marketVendor?->id;
            if ($myVendorId) {
                $this->selectedVendorId = (string) $myVendorId;
            }
        }
    }

    public function isAdminUser(): bool
    {
        return Auth::check() && Auth::user()->hasAnyRole(['super-admin', 'admin']);
    }

    protected function getContextVendorId(): ?int
    {
        if (!$this->isAdminUser()) {
            return Auth::user()?->marketVendor?->id;
        }

        if ($this->selectedWarehouseId) {
            $wh = Warehouse::find($this->selectedWarehouseId);
            if ($wh && $wh->vendor_id) {
                return (int) $wh->vendor_id;
            }
        }

        if ($this->selectedVendorId) {
            return (int) $this->selectedVendorId;
        }

        return Auth::user()?->marketVendor?->id ?? Vendor::first()?->id;
    }

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

    public function updatedBrandFilter()
    {
        $this->categoryFilter = '';
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function updatingTxSearch()
    {
        $this->resetPage('txPage');
    }

    public function updatedTxFilterWarehouseId()
    {
        $this->resetPage('txPage');
    }

    public function updatedTxFilterType()
    {
        $this->resetPage('txPage');
    }

    public function updatingTfSearch()
    {
        $this->resetPage('tfPage');
    }

    public function updatedTfFilterStatus()
    {
        $this->resetPage('tfPage');
    }

    public function resetProductFilters()
    {
        $this->search = '';
        $this->stockStatusFilter = 'all';
        $this->brandFilter = '';
        $this->categoryFilter = '';
        $this->sortBy = 'latest';
        $this->resetPage();
    }

    public function setStockStatusFilter($status)
    {
        $this->stockStatusFilter = $status;
        $this->resetPage();
    }

    public function setWarehouseFilter($filter)
    {
        $this->warehouseFilter = $filter;
        $this->selectedVendorId = '';
        $this->selectedWarehouseId = '';
        if ($filter !== 'vendors' && $this->currentTab === 'warehouses') {
            $this->currentTab = 'stock';
        }
        $this->cancelEdit();
        $this->closeTransactionForm();
        $this->resetPage();
    }

    public function updatedSelectedVendorId()
    {
        $this->selectedWarehouseId = '';
        $this->cancelEdit();
        $this->resetPage();
    }

    public function updatedSelectedWarehouseId()
    {
        $this->cancelEdit();
        $this->resetPage();
    }

    public function selectWarehouseAndTab($warehouseId, $tab)
    {
        $this->selectedWarehouseId = (string) $warehouseId;
        $wh = Warehouse::find($warehouseId);
        if ($wh && $wh->vendor_id && $this->isAdminUser()) {
            $this->warehouseFilter = 'vendors';
            $this->selectedVendorId = (string) $wh->vendor_id;
        }
        $this->currentTab = $tab;
        $this->cancelEdit();
        $this->resetPage();
    }

    public function clearWarehouseFilter()
    {
        $this->selectedWarehouseId = '';
        $this->cancelEdit();
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->currentTab = $tab;
        if ($tab === 'transactions') {
            $this->resetPage('txPage');
        } elseif ($tab === 'transfers') {
            $this->resetPage('tfPage');
        } else {
            $this->resetPage();
        }
    }

    #[Computed]
    public function relevantWarehouses()
    {
        $query = Warehouse::where('is_active', true)->with('vendor');

        if (!$this->isAdminUser()) {
            $vendorId = Auth::user()?->marketVendor?->id;
            $query->where('vendor_id', $vendorId ?: -1);
            if ($this->selectedWarehouseId) {
                $query->where('id', $this->selectedWarehouseId);
            }
        } else {
            if ($this->warehouseFilter === 'central') {
                $query->whereNull('vendor_id');
                if ($this->selectedWarehouseId) {
                    $query->where('id', $this->selectedWarehouseId);
                }
            } elseif ($this->warehouseFilter === 'vendors') {
                $query->whereNotNull('vendor_id');
                if ($this->selectedWarehouseId) {
                    $query->where('id', $this->selectedWarehouseId);
                } elseif ($this->selectedVendorId) {
                    $query->where('vendor_id', $this->selectedVendorId);
                }
            } else {
                // 'all'
                if ($this->selectedWarehouseId) {
                    $query->where('id', $this->selectedWarehouseId);
                } elseif ($this->selectedVendorId) {
                    $query->where('vendor_id', $this->selectedVendorId);
                }
            }
        }

        return $query->get();
    }

    #[Computed]
    public function userAccessibleWarehouses()
    {
        if (!$this->isAdminUser()) {
            $vendorId = Auth::user()?->marketVendor?->id;
            return Warehouse::where('is_active', true)
                ->where('vendor_id', $vendorId ?: -1)
                ->get();
        }

        $query = Warehouse::where('is_active', true)->with('vendor');
        if ($this->warehouseFilter === 'central') {
            $query->whereNull('vendor_id');
        } elseif ($this->warehouseFilter === 'vendors') {
            $query->whereNotNull('vendor_id');
            if ($this->selectedVendorId) {
                $query->where('vendor_id', $this->selectedVendorId);
            }
        }
        return $query->get();
    }

    #[Computed]
    public function allDestinationWarehouses()
    {
        return Warehouse::where('is_active', true)->with('vendor')->orderByRaw('vendor_id IS NOT NULL')->get();
    }

    #[Computed]
    public function wmsStats()
    {
        $warehouseIds = $this->relevantWarehouses()->pluck('id');

        $stocksQuery = WarehouseStock::whereIn('warehouse_id', $warehouseIds);

        return [
            'total_warehouses' => $warehouseIds->count(),
            'active_warehouses' => Warehouse::whereIn('id', $warehouseIds)->where('is_active', true)->count(),
            'total_physical' => (int) (clone $stocksQuery)->sum('physical_stock'),
            'total_online' => (int) (clone $stocksQuery)->sum('online_stock'),
            'total_reserved' => (int) (clone $stocksQuery)->sum('reserved_stock'),
            'discrepancy_count' => (int) (clone $stocksQuery)->whereColumn('physical_stock', '!=', 'online_stock')->count(),
        ];
    }

    #[Computed]
    public function vendorsList()
    {
        return Vendor::where('status', 'active')->get();
    }

    #[Computed]
    public function brandsList()
    {
        return Brand::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function categoriesList()
    {
        $query = Category::where('is_active', true)->orderBy('name');
        if ($this->brandFilter) {
            $query->where('brand_id', $this->brandFilter);
        }
        return $query->get();
    }

    #[Computed]
    public function allWarehousesList()
    {
        $query = Warehouse::with('vendor');
        if (!$this->isAdminUser()) {
            $query->where('vendor_id', Auth::user()?->marketVendor?->id ?: -1);
        } else {
            if ($this->warehouseFilter === 'central') {
                $query->whereNull('vendor_id');
            } elseif ($this->warehouseFilter === 'vendors') {
                $query->whereNotNull('vendor_id');
                if ($this->selectedVendorId) {
                    $query->where('vendor_id', $this->selectedVendorId);
                }
            }
        }
        return $query->get();
    }

    #[Computed]
    public function vendorWarehousesStats()
    {
        $query = Warehouse::whereNotNull('vendor_id')->with('vendor');
        if (!$this->isAdminUser()) {
            $query->where('vendor_id', Auth::user()?->marketVendor?->id ?: -1);
        } elseif ($this->selectedVendorId) {
            $query->where('vendor_id', $this->selectedVendorId);
        }

        return $query->get()->map(function ($wh) {
            $stocks = WarehouseStock::where('warehouse_id', $wh->id)->get();
            return [
                'id' => $wh->id,
                'name' => $wh->name,
                'code' => $wh->code,
                'is_active' => $wh->is_active,
                'vendor_name' => $wh->vendor->store_name ?? 'بدون فروشنده',
                'total_products' => $stocks->count(),
                'total_physical' => (int) $stocks->sum('physical_stock'),
                'total_online' => (int) $stocks->sum('online_stock'),
                'total_reserved' => (int) $stocks->sum('reserved_stock'),
            ];
        });
    }

    #[Computed]
    public function products()
    {
        $relevantWarehouseIds = $this->relevantWarehouses()->pluck('id');
        $isPureVendor = !$this->isAdminUser();
        $vendorId = $this->getContextVendorId();

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
                DB::raw('SUM(stock) as legacy_stock'),
                DB::raw('MAX(price) as vendor_price'),
                DB::raw('MAX(discount_price) as vendor_discount_price'),
                DB::raw('MAX(reorder_point) as vendor_reorder_point'),
                DB::raw('MAX(status) as vendor_status')
            )
            ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
            ->groupBy('product_variant_id');

        $query = ProductVariant::query()
            ->leftJoinSub($wmsSubQuery, 'wms_summary', function ($join) {
                $join->on('market_product_variants.id', '=', 'wms_summary.product_variant_id');
            })
            ->leftJoinSub($vpSubQuery, 'vp_summary', function ($join) {
                $join->on('market_product_variants.id', '=', 'vp_summary.product_variant_id');
            });

        if ($isPureVendor) {
            $query->where(function ($q) {
                $q->whereNotNull('vp_summary.vendor_product_id')
                  ->orWhereNotNull('wms_summary.product_variant_id');
            });
        } elseif ($this->warehouseFilter === 'vendors' && ($this->selectedVendorId || $this->selectedWarehouseId)) {
            $query->where(function ($q) {
                $q->whereNotNull('vp_summary.vendor_product_id')
                  ->orWhereNotNull('wms_summary.product_variant_id');
            });
        }

        $query->select(
            'market_product_variants.*',
            'vp_summary.vendor_product_id',
            'vp_summary.legacy_stock',
            'vp_summary.vendor_price',
            'vp_summary.vendor_discount_price',
            'vp_summary.vendor_reorder_point',
            'vp_summary.vendor_status',
            DB::raw('COALESCE(wms_summary.total_physical_stock, 0) as total_physical_stock'),
            DB::raw('COALESCE(wms_summary.total_online_stock, 0) as total_online_stock'),
            DB::raw('COALESCE(wms_summary.total_reserved_stock, 0) as total_reserved_stock')
        );

        $query->with([
            'masterProduct.brand',
            'masterProduct.category',
            'warehouseStocks' => function ($q) use ($relevantWarehouseIds) {
                $q->whereIn('warehouse_id', $relevantWarehouseIds);
            },
        ]);

        if ($this->search) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->whereHas('masterProduct', function ($q2) use ($term) {
                    $q2->where('title', 'like', '%' . $term . '%')
                       ->orWhere('crm_code', 'like', '%' . $term . '%')
                       ->orWhere('barcode', 'like', '%' . $term . '%')
                       ->orWhere('gtin', 'like', '%' . $term . '%');
                })
                ->orWhere('market_product_variants.variant_code', 'like', '%' . $term . '%')
                ->orWhere('market_product_variants.variant_attributes', 'like', '%' . $term . '%');
            });
        }

        if ($this->brandFilter) {
            $brandId = (int) $this->brandFilter;
            $query->whereHas('masterProduct', function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
        }

        if ($this->categoryFilter) {
            $catId = (int) $this->categoryFilter;
            // Include child categories up to 4 levels
            $childIds = Category::where('parent_id', $catId)->pluck('id')->toArray();
            $grandChildIds = !empty($childIds) ? Category::whereIn('parent_id', $childIds)->pluck('id')->toArray() : [];
            $greatGrandChildIds = !empty($grandChildIds) ? Category::whereIn('parent_id', $grandChildIds)->pluck('id')->toArray() : [];
            $allCatIds = array_unique(array_merge([$catId], $childIds, $grandChildIds, $greatGrandChildIds));

            $query->whereHas('masterProduct', function ($q) use ($allCatIds) {
                $q->whereIn('category_id', $allCatIds);
            });
        }

        // Use WHERE on the joined subquery columns so pagination count and data queries both work accurately
        if ($this->stockStatusFilter === 'in_stock') {
            $query->whereRaw('(COALESCE(wms_summary.total_physical_stock, 0) + COALESCE(wms_summary.total_online_stock, 0)) > 0');
        } elseif ($this->stockStatusFilter === 'out_of_stock') {
            $query->whereRaw('(COALESCE(wms_summary.total_physical_stock, 0) + COALESCE(wms_summary.total_online_stock, 0)) = 0');
        } elseif ($this->stockStatusFilter === 'low_stock') {
            $query->whereRaw('COALESCE(wms_summary.total_physical_stock, 0) > 0 AND COALESCE(wms_summary.total_physical_stock, 0) <= COALESCE(vp_summary.vendor_reorder_point, market_product_variants.reorder_point, 5)');
        } elseif ($this->stockStatusFilter === 'has_reserved') {
            $query->whereRaw('COALESCE(wms_summary.total_reserved_stock, 0) > 0');
        } elseif ($this->stockStatusFilter === 'discrepancy') {
            $query->whereRaw('COALESCE(wms_summary.total_physical_stock, 0) != COALESCE(wms_summary.total_online_stock, 0)');
        }

        // Sorting
        switch ($this->sortBy) {
            case 'physical_desc':
                $query->orderByDesc('total_physical_stock');
                break;
            case 'physical_asc':
                $query->orderBy('total_physical_stock', 'asc');
                break;
            case 'online_desc':
                $query->orderByDesc('total_online_stock');
                break;
            case 'reserved_desc':
                $query->orderByDesc('total_reserved_stock');
                break;
            default:
                $query->orderByDesc('market_product_variants.id');
                break;
        }

        return $query->paginate(10);
    }

    public function toggleEditStock($variantId)
    {
        if ($this->editingVariantId === $variantId && !$this->showTxVariantId) {
            $this->cancelEdit();
            return;
        }

        $this->showTxVariantId = null;
        $this->editingVariantId = $variantId;
        $this->editableStocks = [];

        $relevantWarehouses = $this->relevantWarehouses();
        $relevantWarehouseIds = $relevantWarehouses->pluck('id');

        $existingStocks = WarehouseStock::where('product_variant_id', $variantId)
            ->whereIn('warehouse_id', $relevantWarehouseIds)
            ->get()
            ->keyBy('warehouse_id');

        foreach ($relevantWarehouses as $warehouse) {
            $stock = $existingStocks->get($warehouse->id);

            $this->editableStocks[$warehouse->id] = [
                'physical_stock' => (int) ($stock->physical_stock ?? 0),
                'online_stock' => (int) ($stock->online_stock ?? 0),
            ];
        }
    }

    public function adjustEditableStock($warehouseId, $field, $delta)
    {
        if (!isset($this->editableStocks[$warehouseId][$field])) {
            $this->editableStocks[$warehouseId][$field] = 0;
        }
        $current = (int) $this->editableStocks[$warehouseId][$field];
        $this->editableStocks[$warehouseId][$field] = max(0, $current + (int) $delta);
    }

    public function syncOnlineWithPhysical($warehouseId)
    {
        if (isset($this->editableStocks[$warehouseId]['physical_stock'])) {
            $this->editableStocks[$warehouseId]['online_stock'] = (int) $this->editableStocks[$warehouseId]['physical_stock'];
        }
    }

    public function cancelEdit()
    {
        $this->editingVariantId = null;
        $this->editableStocks = [];
        $this->resetValidation();
    }

    public function saveStocksForVariant($variantId = null)
    {
        $variantId = $variantId ?: $this->editingVariantId;
        $this->validate();

        $variant = ProductVariant::findOrFail($variantId);
        $userId = Auth::id();
        $isPureVendor = !$this->isAdminUser();
        $vendorId = $this->getContextVendorId();

        DB::beginTransaction();
        try {
            $affectedVendorProducts = [];

            foreach ($this->editableStocks as $warehouseId => $data) {
                $warehouseQuery = Warehouse::where('id', $warehouseId);
                if ($isPureVendor) {
                    $warehouseQuery->where('vendor_id', $vendorId);
                }

                $warehouse = $warehouseQuery->first();
                if (!$warehouse) {
                    continue;
                }

                $whVendorId = $warehouse->vendor_id ?? $vendorId;

                $whVendorProduct = null;
                if ($whVendorId) {
                    $whVendorProduct = VendorProduct::firstOrCreate(
                        ['vendor_id' => $whVendorId, 'product_variant_id' => $variant->id],
                        ['status' => 'draft', 'price' => $variant->price ?? 0]
                    );
                }

                $currentStock = WarehouseStock::firstOrNew([
                    'warehouse_id' => $warehouseId,
                    'product_variant_id' => $variant->id,
                ]);
                if ($whVendorProduct && !$currentStock->vendor_product_id) {
                    $currentStock->vendor_product_id = $whVendorProduct->id;
                }

                $oldPhysical = (int) ($currentStock->physical_stock ?? 0);
                $oldOnline = (int) ($currentStock->online_stock ?? 0);

                $newPhysical = max(0, (int) $data['physical_stock']);
                $newOnline = max(0, (int) $data['online_stock']);

                if (!$currentStock->exists && $newPhysical === 0 && $newOnline === 0) {
                    continue;
                }

                $currentStock->physical_stock = $newPhysical;
                $currentStock->online_stock = $newOnline;
                $currentStock->save();

                if ($whVendorId && $whVendorProduct) {
                    $affectedVendorProducts[$whVendorId] = $whVendorProduct;
                }

                if ($oldPhysical !== $newPhysical) {
                    WarehouseTransaction::create([
                        'warehouse_id' => $warehouseId,
                        'product_variant_id' => $variant->id,
                        'vendor_product_id' => $whVendorProduct?->id,
                        'type' => 'adjustment',
                        'quantity' => $newPhysical - $oldPhysical,
                        'description' => "تعدیل انبارگردانی (موجودی فیزیکی از {$oldPhysical} به {$newPhysical})",
                        'user_id' => $userId,
                    ]);
                }

                if ($oldOnline !== $newOnline) {
                    WarehouseTransaction::create([
                        'warehouse_id' => $warehouseId,
                        'product_variant_id' => $variant->id,
                        'vendor_product_id' => $whVendorProduct?->id,
                        'type' => 'adjustment',
                        'quantity' => $newOnline - $oldOnline,
                        'description' => "تعدیل انبارگردانی (موجودی آنلاین از {$oldOnline} به {$newOnline})",
                        'user_id' => $userId,
                    ]);
                }
            }

            foreach ($affectedVendorProducts as $vId => $vProd) {
                $newStock = app(\Modules\Market\App\Services\WarehouseStockService::class)
                    ->getAvailableStock($variant->id, $vId);
                $vProd->update(['stock' => $newStock]);
                $variant->update(['stock' => $newStock]);
            }

            DB::commit();

            $this->dispatch('notify', type: 'success', text: 'موجودی انبار با موفقیت بروزرسانی شد و سند تعدیل انبارگردانی ثبت گردید.');
            $this->cancelEdit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', text: 'خطایی در بروزرسانی موجودی رخ داد: ' . $e->getMessage());
        }
    }

    public function openTransactionForm($variantId, $warehouseId = null)
    {
        if ($this->showTxVariantId === $variantId && ($warehouseId === null || $this->txWarehouseId == $warehouseId)) {
            $this->closeTransactionForm();
            return;
        }
        $this->showTxVariantId = $variantId;
        $this->cancelEdit();

        $relevant = $this->relevantWarehouses();
        $this->txWarehouseId = $warehouseId ?? ($relevant->first()?->id ?? '');
        $this->txType = 'in';
        $this->txStockType = 'both';
        $this->txQuantity = '';
        $this->txUnitPrice = '';
        $this->txReason = 'purchase';
        $this->txCustomDescription = '';
        $this->txDocumentRef = '';
        $this->resetValidation();
    }

    public function closeTransactionForm()
    {
        $this->showTxVariantId = null;
        $this->resetValidation();
    }

    public function submitTransaction($variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);

        if (is_string($this->txUnitPrice)) {
            $this->txUnitPrice = str_replace(',', '', $this->txUnitPrice);
        }

        $rules = [
            'txWarehouseId' => 'required|exists:market_warehouses,id',
            'txType' => 'required|in:in,out',
            'txStockType' => 'required|in:physical,online,both',
            'txQuantity' => 'required|integer|min:1',
            'txUnitPrice' => 'nullable|numeric|min:0',
            'txReason' => 'required|string',
            'txCustomDescription' => 'nullable|string|max:500',
            'txDocumentRef' => 'nullable|string|max:100',
        ];

        $this->validate($rules, [], [
            'txWarehouseId' => 'انبار',
            'txType' => 'نوع حواله',
            'txStockType' => 'نوع موجودی هدف',
            'txQuantity' => 'تعداد/مقدار',
            'txUnitPrice' => $this->txType === 'in' ? 'قیمت خرید' : 'قیمت فروش',
            'txReason' => 'علت حواله',
            'txCustomDescription' => 'توضیحات اختصاصی',
            'txDocumentRef' => 'شماره سند مرجع',
        ]);

        $warehouse = Warehouse::findOrFail($this->txWarehouseId);

        if (!$this->isAdminUser()) {
            if ($warehouse->vendor_id !== Auth::user()?->marketVendor?->id) {
                throw new \Exception('شما مجاز به ثبت حواله برای این انبار نیستید.');
            }
        }

        $vendorId = $warehouse->vendor_id ?? $this->getContextVendorId();

        DB::beginTransaction();
        try {
            $vendorProduct = null;
            if ($vendorId) {
                $vendorProduct = VendorProduct::firstOrCreate(
                    ['vendor_id' => $vendorId, 'product_variant_id' => $variant->id],
                    ['status' => 'draft', 'price' => $variant->price ?? 0]
                );
            }

            $stock = WarehouseStock::firstOrNew([
                'warehouse_id' => $this->txWarehouseId,
                'product_variant_id' => $variant->id,
            ], [
                'physical_stock' => 0,
                'online_stock' => 0,
                'reserved_stock' => 0,
            ]);
            if ($vendorProduct && !$stock->vendor_product_id) {
                $stock->vendor_product_id = $vendorProduct->id;
            }

            $qty = (int) $this->txQuantity;
            $price = $this->txUnitPrice ? (float) $this->txUnitPrice : null;

            $physicalChange = ($this->txStockType === 'physical' || $this->txStockType === 'both') ? $qty : 0;
            $onlineChange = ($this->txStockType === 'online' || $this->txStockType === 'both') ? $qty : 0;

            if ($this->txType === 'out') {
                if ($physicalChange > 0 && ($stock->physical_stock ?? 0) < $physicalChange) {
                    throw new \Exception('موجودی فیزیکی انبار برای این خروج کافی نیست.');
                }
                if ($onlineChange > 0 && (($stock->online_stock ?? 0) - ($stock->reserved_stock ?? 0)) < $onlineChange) {
                    throw new \Exception('موجودی آنلاین آزاد انبار برای این خروج کافی نیست.');
                }

                $stock->physical_stock = ($stock->physical_stock ?? 0) - $physicalChange;
                $stock->online_stock = ($stock->online_stock ?? 0) - $onlineChange;
            } else {
                $stock->physical_stock = ($stock->physical_stock ?? 0) + $physicalChange;
                $stock->online_stock = ($stock->online_stock ?? 0) + $onlineChange;
            }

            $stock->save();

            $reasons = [
                'purchase' => 'خرید کالا',
                'return' => 'مرجوعی مشتری',
                'adjustment_in' => 'تعدیل مثبت (ورود)',
                'sale' => 'فروش دستی کالا',
                'damage' => 'ضایعات/خرابی',
                'adjustment_out' => 'تعدیل منفی (خروج)',
                'other' => 'سایر',
            ];
            $reasonText = $reasons[$this->txReason] ?? $this->txReason;
            $descriptionText = '[' . $reasonText . ']';
            if ($this->txDocumentRef) {
                $descriptionText .= ' - سند مرجع: ' . $this->txDocumentRef;
            }
            if ($this->txCustomDescription) {
                $descriptionText .= ' - ' . $this->txCustomDescription;
            }

            WarehouseTransaction::create([
                'warehouse_id' => $this->txWarehouseId,
                'product_variant_id' => $variant->id,
                'vendor_product_id' => $vendorProduct?->id,
                'type' => $this->txType,
                'quantity' => $this->txType === 'out' ? -$qty : $qty,
                'unit_price' => $price,
                'description' => $descriptionText,
                'user_id' => Auth::id(),
            ]);

            if ($vendorProduct) {
                $newStock = app(\Modules\Market\App\Services\WarehouseStockService::class)
                    ->getAvailableStock($variant->id, $vendorId);
                $vendorProduct->update(['stock' => $newStock]);
                $variant->update(['stock' => $newStock]);
            }

            DB::commit();

            $this->dispatch('notify', type: 'success', text: 'حواله دستی انبار با موفقیت ثبت شد.');
            $this->closeTransactionForm();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', text: 'خطا در ثبت حواله دستی انبار: ' . $e->getMessage());
        }
    }

    #[Computed]
    public function warehouseTransactions()
    {
        $relevantWarehouseIds = $this->relevantWarehouses()->pluck('id');

        return WarehouseTransaction::with(['warehouse', 'productVariant.masterProduct', 'user'])
            ->whereIn('warehouse_id', $relevantWarehouseIds)
            ->when($this->txFilterWarehouseId, function ($q) {
                $q->where('warehouse_id', $this->txFilterWarehouseId);
            })
            ->when($this->txFilterType !== 'all', function ($q) {
                $q->where('type', $this->txFilterType);
            })
            ->when($this->txSearch, function ($q) {
                $term = trim($this->txSearch);
                $q->where(function ($q2) use ($term) {
                    $q2->where('description', 'like', '%' . $term . '%')
                       ->orWhereHas('productVariant.masterProduct', function ($q3) use ($term) {
                           $q3->where('title', 'like', '%' . $term . '%')
                              ->orWhere('crm_code', 'like', '%' . $term . '%')
                              ->orWhere('barcode', 'like', '%' . $term . '%');
                       })
                       ->orWhereHas('productVariant', function ($q3) use ($term) {
                           $q3->where('variant_code', 'like', '%' . $term . '%');
                       });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'txPage');
    }

    // --- Inter-Warehouse Transfer Actions ---

    public function openTransferForm($variantId, $warehouseId = null)
    {
        $this->showTransferVariantId = $variantId;
        $this->cancelEdit();
        $this->closeTransactionForm();

        $relevant = $this->relevantWarehouses();
        $this->tfSourceWarehouseId = $warehouseId ?? ($relevant->first()?->id ?? '');

        $centralWarehouse = Warehouse::whereNull('vendor_id')->where('is_active', true)->first();
        if ($centralWarehouse && $centralWarehouse->id != $this->tfSourceWarehouseId) {
            $this->tfDestinationWarehouseId = $centralWarehouse->id;
        } else {
            $otherWh = Warehouse::where('is_active', true)->where('id', '!=', $this->tfSourceWarehouseId)->first();
            $this->tfDestinationWarehouseId = $otherWh ? $otherWh->id : '';
        }
        $this->tfQuantity = '';

        $this->resetValidation();
    }

    public function closeTransferForm()
    {
        $this->showTransferVariantId = null;
        $this->resetValidation();
    }

    public function submitTransferRequest($variantId)
    {
        ProductVariant::findOrFail($variantId);

        $rules = [
            'tfSourceWarehouseId' => 'required|exists:market_warehouses,id',
            'tfDestinationWarehouseId' => 'required|exists:market_warehouses,id|different:tfSourceWarehouseId',
            'tfQuantity' => 'required|integer|min:1',
        ];

        $this->validate($rules, [], [
            'tfSourceWarehouseId' => 'انبار مبدا',
            'tfDestinationWarehouseId' => 'انبار مقصد',
            'tfQuantity' => 'تعداد انتقال',
        ]);

        $sourceWarehouse = Warehouse::findOrFail($this->tfSourceWarehouseId);

        if (!$this->isAdminUser()) {
            if ($sourceWarehouse->vendor_id !== Auth::user()?->marketVendor?->id) {
                throw new \Exception('شما مجاز به درخواست انتقال از این انبار نیستید.');
            }
        }

        $stock = WarehouseStock::where('warehouse_id', $this->tfSourceWarehouseId)
            ->where('product_variant_id', $variantId)
            ->first();

        if (!$stock || $stock->physical_stock < (int) $this->tfQuantity) {
            $this->addError('tfQuantity', 'موجودی فیزیکی کافی در انبار مبدا وجود ندارد.');
            return;
        }

        $vendorId = $sourceWarehouse->vendor_id;
        $vendorProduct = $vendorId
            ? VendorProduct::where('vendor_id', $vendorId)->where('product_variant_id', $variantId)->first()
            : null;

        DB::beginTransaction();
        try {
            WarehouseTransfer::create([
                'source_warehouse_id' => $this->tfSourceWarehouseId,
                'destination_warehouse_id' => $this->tfDestinationWarehouseId,
                'product_variant_id' => $variantId,
                'vendor_product_id' => $vendorProduct ? $vendorProduct->id : null,
                'quantity' => (int) $this->tfQuantity,
                'status' => 'pending',
                'user_id' => Auth::id(),
            ]);

            DB::commit();

            $this->dispatch('notify', type: 'success', text: 'درخواست انتقال با موفقیت ثبت شد و در انتظار تایید مدیریت است.');
            $this->closeTransferForm();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', text: 'خطا در ثبت درخواست انتقال: ' . $e->getMessage());
        }
    }

    public function approveTransferRequest($transferId)
    {
        if (!$this->isAdminUser()) {
            $this->dispatch('notify', type: 'error', text: 'شما دسترسی لازم برای تایید این درخواست را ندارید.');
            return;
        }

        $transfer = WarehouseTransfer::findOrFail($transferId);
        if ($transfer->status !== 'pending') {
            $this->dispatch('notify', type: 'error', text: 'این درخواست قبلاً پردازش شده است.');
            return;
        }

        DB::beginTransaction();
        try {
            app(\Modules\Market\App\Services\WarehouseStockService::class)
                ->transferStock($transfer, Auth::id());

            $transfer->update([
                'status' => 'approved',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            DB::commit();

            $this->dispatch('notify', type: 'success', text: 'درخواست انتقال با موفقیت تایید و موجودی‌ها بروزرسانی شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', text: 'خطا در تایید انتقال: ' . $e->getMessage());
        }
    }

    public function openRejectTransferForm($transferId)
    {
        $this->showRejectTransferId = $transferId;
        $this->rejectReason = '';
        $this->resetValidation();
    }

    public function closeRejectTransferForm()
    {
        $this->showRejectTransferId = null;
        $this->resetValidation();
    }

    public function submitRejection()
    {
        if (!$this->isAdminUser()) {
            $this->dispatch('notify', type: 'error', text: 'شما دسترسی لازم را ندارید.');
            return;
        }

        $this->validate([
            'rejectReason' => 'required|string|max:500',
        ], [], [
            'rejectReason' => 'علت رد درخواست',
        ]);

        $transfer = WarehouseTransfer::findOrFail($this->showRejectTransferId);
        if ($transfer->status !== 'pending') {
            $this->dispatch('notify', type: 'error', text: 'این درخواست قبلاً پردازش شده است.');
            return;
        }

        $transfer->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectReason,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        $this->dispatch('notify', type: 'success', text: 'درخواست انتقال با موفقیت رد شد.');
        $this->closeRejectTransferForm();
    }

    #[Computed]
    public function warehouseTransfers()
    {
        $query = WarehouseTransfer::with(['sourceWarehouse.vendor', 'destinationWarehouse', 'productVariant.masterProduct', 'user', 'processor']);

        if (!$this->isAdminUser()) {
            $vendorId = Auth::user()?->marketVendor?->id;
            $query->where(function ($q) use ($vendorId) {
                $q->whereHas('sourceWarehouse', fn($sq) => $sq->where('vendor_id', $vendorId ?: -1))
                  ->orWhereHas('destinationWarehouse', fn($dq) => $dq->where('vendor_id', $vendorId ?: -1));
            });
        } else {
            if ($this->selectedWarehouseId) {
                $whId = (int) $this->selectedWarehouseId;
                $query->where(function ($q) use ($whId) {
                    $q->where('source_warehouse_id', $whId)
                      ->orWhere('destination_warehouse_id', $whId);
                });
            } elseif ($this->warehouseFilter === 'central') {
                $query->where(function ($q) {
                    $q->whereHas('destinationWarehouse', fn($dq) => $dq->whereNull('vendor_id'))
                      ->orWhereHas('sourceWarehouse', fn($sq) => $sq->whereNull('vendor_id'));
                });
            } elseif ($this->warehouseFilter === 'vendors') {
                $query->where(function ($q) {
                    $q->whereHas('sourceWarehouse', fn($sq) => $sq->whereNotNull('vendor_id'))
                      ->orWhereHas('destinationWarehouse', fn($dq) => $dq->whereNotNull('vendor_id'));
                });
            }
        }

        if ($this->tfFilterStatus && $this->tfFilterStatus !== 'all') {
            $query->where('status', $this->tfFilterStatus);
        }

        if ($this->tfSearch) {
            $term = trim($this->tfSearch);
            $query->where(function ($q) use ($term) {
                $q->where('id', $term)
                  ->orWhereHas('productVariant.masterProduct', function ($q2) use ($term) {
                      $q2->where('title', 'like', '%' . $term . '%')
                         ->orWhere('crm_code', 'like', '%' . $term . '%')
                         ->orWhere('barcode', 'like', '%' . $term . '%');
                  })
                  ->orWhereHas('productVariant', function ($q2) use ($term) {
                      $q2->where('variant_code', 'like', '%' . $term . '%');
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15, ['*'], 'tfPage');
    }

    public function render()
    {
        return view('market::livewire.admin.warehouse-stock-controller');
    }
}
