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
    public $stockStatusFilter = 'all';

    #[Url]
    public $warehouseFilter = 'central'; // 'central', 'vendors', 'all'

    #[Url]
    public $selectedVendorId = '';

    #[Url]
    public $selectedWarehouseId = '';

    public $editingVariantId = null;
    public $editableStocks = [];

    // Properties for transactions listing tab
    public $currentTab = 'stock'; // 'stock' or 'transactions'
    public $txFilterWarehouseId = '';
    public $txFilterType = 'all';
    public $txSearch = '';

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

    public function setWarehouseFilter($filter)
    {
        $this->warehouseFilter = $filter;
        $this->selectedVendorId = '';
        $this->selectedWarehouseId = '';
        if ($filter !== 'vendors' && $this->currentTab === 'warehouses') {
            $this->currentTab = 'stock';
        }
        $this->resetPage();
    }

    public function updatedSelectedVendorId()
    {
        $this->selectedWarehouseId = '';
        $this->resetPage();
    }

    public function updatedSelectedWarehouseId()
    {
        $this->resetPage();
    }

    public function selectWarehouseAndTab($warehouseId, $tab)
    {
        $this->selectedWarehouseId = $warehouseId;
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function clearWarehouseFilter()
    {
        $this->selectedWarehouseId = '';
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->currentTab = $tab;
        if ($tab === 'transactions') {
            $this->txFilterWarehouseId = '';
            $this->txFilterType = 'all';
            $this->txSearch = '';
        }
        $this->resetPage();
    }

    #[Computed]
    public function relevantWarehouses()
    {
        $query = Warehouse::where('is_active', true);

        if (Auth::user()->hasRole('vendor') && !Auth::user()->hasAnyRole(['super-admin', 'admin'])) {
            $query->where('vendor_id', Auth::user()->marketVendor->id);
        } else {
            // Admin/Super Admin
            if ($this->warehouseFilter === 'central') {
                $query->whereNull('vendor_id');
            } elseif ($this->warehouseFilter === 'vendors') {
                $query->whereNotNull('vendor_id');
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
    public function wmsStats()
    {
        $warehouseIds = $this->relevantWarehouses()->pluck('id');

        return [
            'total_warehouses' => $warehouseIds->count(),
            'active_warehouses' => Warehouse::whereIn('id', $warehouseIds)->where('is_active', true)->count(),
            'total_physical' => (int) WarehouseStock::whereIn('warehouse_id', $warehouseIds)->sum('physical_stock'),
            'total_online' => (int) WarehouseStock::whereIn('warehouse_id', $warehouseIds)->sum('online_stock'),
            'total_reserved' => (int) WarehouseStock::whereIn('warehouse_id', $warehouseIds)->sum('reserved_stock'),
        ];
    }

    #[Computed]
    public function vendorsList()
    {
        return \Modules\Market\Entities\Vendor::where('status', 'active')->get();
    }

    #[Computed]
    public function allWarehousesList()
    {
        $query = Warehouse::whereNotNull('vendor_id')->with('vendor');
        if ($this->selectedVendorId) {
            $query->where('vendor_id', $this->selectedVendorId);
        }
        return $query->get();
    }

    #[Computed]
    public function vendorWarehousesStats()
    {
        $query = Warehouse::whereNotNull('vendor_id')->with('vendor');
        if ($this->selectedVendorId) {
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
                'total_physical' => $stocks->sum('physical_stock'),
                'total_online' => $stocks->sum('online_stock'),
                'total_reserved' => $stocks->sum('reserved_stock'),
            ];
        });
    }

    #[Computed]
    public function products()
    {
        $relevantWarehouseIds = $this->relevantWarehouses()->pluck('id');
        $isVendor = Auth::user()->hasRole('vendor');
        $vendorId = $isVendor ? Auth::user()->marketVendor->id : (Auth::user()->marketVendor->id ?? \Modules\Market\Entities\Vendor::first()?->id);

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
            ->where('vendor_id', $vendorId)
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
        $vendorId = $isVendor ? Auth::user()->marketVendor->id : (Auth::user()->marketVendor->id ?? \Modules\Market\Entities\Vendor::first()?->id);

        DB::beginTransaction();
        try {
            $affectedVendorProducts = [];

            // 1. Process and save individual warehouse stock updates
            foreach ($this->editableStocks as $warehouseId => $data) {
                // Security check: ensure the warehouse belongs to the context
                $warehouseQuery = Warehouse::where('id', $warehouseId);
                if ($isVendor) {
                    $warehouseQuery->where('vendor_id', $vendorId);
                }

                $warehouse = $warehouseQuery->first();
                if (!$warehouse) {
                    continue; // Skip if invalid permission
                }

                $whVendorId = $warehouse->vendor_id ?? $vendorId;
                
                // Guarantee a VendorProduct entry exists for this context
                $whVendorProduct = VendorProduct::firstOrCreate(
                    ['vendor_id' => $whVendorId, 'product_variant_id' => $variant->id],
                    ['status' => 'draft', 'price' => 0]
                );

                $currentStock = WarehouseStock::firstOrNew([
                    'warehouse_id' => $warehouseId,
                    'product_variant_id' => $variant->id,
                    'vendor_product_id' => $whVendorProduct->id,
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

                $affectedVendorProducts[$whVendorId] = $whVendorProduct;

                // Log physical transactions
                if ($oldPhysical !== $newPhysical) {
                    WarehouseTransaction::create([
                        'warehouse_id' => $warehouseId,
                        'product_variant_id' => $variant->id,
                        'vendor_product_id' => $whVendorProduct->id,
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
                        'vendor_product_id' => $whVendorProduct->id,
                        'type' => 'adjustment',
                        'quantity' => $newOnline - $oldOnline,
                        'description' => 'تعدیل موجودی آنلاین',
                        'user_id' => $userId,
                    ]);
                }
            }

            // 2. Strict Two-Way Synchronization back to Legacy System (Source of Truth for frontend)
            foreach ($affectedVendorProducts as $vId => $vProd) {
                $newStock = app(\Modules\Market\App\Services\WarehouseStockService::class)
                    ->getAvailableStock($variant->id, $vId);
                $vProd->update(['stock' => $newStock]);
            }

            DB::commit();

            $this->dispatch('notify', type: 'success', text: 'موجودی انبار با موفقیت بروزرسانی شد و با سیستم هماهنگ گردید.');
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
        $this->cancelEdit(); // Close simple stock edit if open

        // Reset properties
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
        $isVendor = Auth::user()->hasRole('vendor');

        if (is_string($this->txUnitPrice)) {
            $this->txUnitPrice = str_replace(',', '', $this->txUnitPrice);
        }

        // Custom validation rules based on transaction type (in/out)
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

        $validated = $this->validate($rules, [], [
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

        // Security check: If the user is a vendor and NOT an admin, they cannot access a warehouse that is not theirs.
        if (Auth::user()->hasRole('vendor') && !Auth::user()->hasAnyRole(['super-admin', 'admin'])) {
            if ($warehouse->vendor_id !== Auth::user()->marketVendor->id) {
                throw new \Exception('شما مجاز به ثبت حواله برای این انبار نیستید.');
            }
        }

        $vendorId = $warehouse->vendor_id ?? ($isVendor ? Auth::user()->marketVendor->id : (Auth::user()->marketVendor->id ?? \Modules\Market\Entities\Vendor::first()?->id));

        DB::beginTransaction();
        try {
            // Guarantee a VendorProduct entry exists for this context
            $vendorProduct = VendorProduct::firstOrCreate(
                ['vendor_id' => $vendorId, 'product_variant_id' => $variant->id],
                ['status' => 'draft', 'price' => 0]
            );

            // Fetch or create stock record
            $stock = WarehouseStock::firstOrNew([
                'warehouse_id' => $this->txWarehouseId,
                'product_variant_id' => $variant->id,
                'vendor_product_id' => $vendorProduct->id,
            ], [
                'physical_stock' => 0,
                'online_stock' => 0,
                'reserved_stock' => 0,
            ]);

            $qty = (int) $this->txQuantity;
            $price = $this->txUnitPrice ? (float) $this->txUnitPrice : null;

            // Compute target stock updates
            $physicalChange = 0;
            $onlineChange = 0;

            if ($this->txStockType === 'physical' || $this->txStockType === 'both') {
                $physicalChange = $qty;
            }
            if ($this->txStockType === 'online' || $this->txStockType === 'both') {
                $onlineChange = $qty;
            }

            if ($this->txType === 'out') {
                // Check if enough stock
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

            // Reasons mapper for description
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

            // Write to WarehouseTransaction
            WarehouseTransaction::create([
                'warehouse_id' => $this->txWarehouseId,
                'product_variant_id' => $variant->id,
                'vendor_product_id' => $vendorProduct->id,
                'type' => $this->txType,
                'quantity' => $this->txType === 'out' ? -$qty : $qty,
                'unit_price' => $price,
                'description' => $descriptionText,
                'user_id' => Auth::id(),
            ]);

            // Sync legacy stock field using strategy
            $newStock = app(\Modules\Market\App\Services\WarehouseStockService::class)
                ->getAvailableStock($variant->id, $vendorId);
            $vendorProduct->update(['stock' => $newStock]);

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
                $q->where(function ($q2) {
                    $q2->where('description', 'like', '%' . $this->txSearch . '%')
                       ->orWhereHas('productVariant.masterProduct', function ($q3) {
                           $q3->where('title', 'like', '%' . $this->txSearch . '%');
                       })
                       ->orWhereHas('productVariant', function ($q3) {
                           $q3->where('variant_code', 'like', '%' . $this->txSearch . '%');
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

        // Destination default is the Central Warehouse (vendor_id is null)
        $centralWarehouse = Warehouse::whereNull('vendor_id')->where('is_active', true)->first();
        $this->tfDestinationWarehouseId = $centralWarehouse ? $centralWarehouse->id : '';
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
        $variant = ProductVariant::findOrFail($variantId);
        
        $rules = [
            'tfSourceWarehouseId' => 'required|exists:market_warehouses,id',
            'tfDestinationWarehouseId' => 'required|exists:market_warehouses,id|different:tfSourceWarehouseId',
            'tfQuantity' => 'required|integer|min:1',
        ];

        $validated = $this->validate($rules, [], [
            'tfSourceWarehouseId' => 'انبار مبدا',
            'tfDestinationWarehouseId' => 'انبار مقصد',
            'tfQuantity' => 'تعداد انتقال',
        ]);

        $sourceWarehouse = Warehouse::findOrFail($this->tfSourceWarehouseId);
        
        // Security check
        if (Auth::user()->hasRole('vendor') && !Auth::user()->hasAnyRole(['super-admin', 'admin'])) {
            if ($sourceWarehouse->vendor_id !== Auth::user()->marketVendor->id) {
                throw new \Exception('شما مجاز به درخواست انتقال از این انبار نیستید.');
            }
        }

        // Check stock availability
        $stock = WarehouseStock::where('warehouse_id', $this->tfSourceWarehouseId)
            ->where('product_variant_id', $variantId)
            ->first();

        if (!$stock || $stock->physical_stock < (int) $this->tfQuantity) {
            $this->addError('tfQuantity', 'موجودی فیزیکی کافی در انبار مبدا وجود ندارد.');
            return;
        }

        $vendorId = $sourceWarehouse->vendor_id;
        $vendorProduct = VendorProduct::where('vendor_id', $vendorId)
            ->where('product_variant_id', $variantId)
            ->first();

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
        // Must be admin
        if (!Auth::user()->hasAnyRole(['super-admin', 'admin'])) {
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
            // Use the stock service to handle actual logic
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
        if (!Auth::user()->hasAnyRole(['super-admin', 'admin'])) {
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

        if (Auth::user()->hasRole('vendor') && !Auth::user()->hasAnyRole(['super-admin', 'admin'])) {
            $vendorId = Auth::user()->marketVendor->id;
            $query->whereHas('sourceWarehouse', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });
        } else {
            // Admin context
            if ($this->warehouseFilter === 'central') {
                $query->whereHas('destinationWarehouse', function ($q) {
                    $q->whereNull('vendor_id');
                });
            } elseif ($this->warehouseFilter === 'vendors') {
                $query->whereHas('sourceWarehouse', function ($q) {
                    $q->whereNotNull('vendor_id');
                });
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate(15, ['*'], 'tfPage');
    }

    public function render()
    {
        return view('market::livewire.admin.warehouse-stock-controller');
    }
}
