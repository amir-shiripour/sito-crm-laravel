<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\Warehouse;
use Modules\Market\Entities\WarehouseStock;
use Modules\Market\App\Services\WarehouseStockService;
use Livewire\Attributes\On;

class WarehouseStockController extends Component
{
    use WithPagination;

    public $warehouseId;
    public $search = '';

    // Modal: Adjust Stock
    public $isModalOpen = false;
    public $stockId;
    public $physical_stock;
    public $reserved_stock;
    public $adjustment_quantity;
    public $adjustment_description;

    // Modal: Add Product
    public $isAddProductModalOpen = false;
    // public $productSearch = ''; // 💡 حذف شد
    // public $productSearchResults = []; // 💡 حذف شد
    public $selectedVariantId = null; // 💡 مقداردهی اولیه به null
    public $initialStock;
    public $initialStockDescription = 'Initial stock count';

    protected function rules()
    {
        return [
            'adjustment_quantity' => 'required|integer|not_in:0',
            'adjustment_description' => 'required|string|max:500',
            'selectedVariantId' => 'required|exists:market_product_variants,id',
            'initialStock' => 'required|integer|min:0',
            'initialStockDescription' => 'required|string|max:500',
        ];
    }

    public function mount($warehouseId)
    {
        $this->warehouseId = $warehouseId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // 💡 متد updatedProductSearch حذف شد

    public function edit($stockId)
    {
        $stock = WarehouseStock::findOrFail($stockId);
        $this->stockId = $stock->id;
        $this->physical_stock = $stock->physical_stock;
        $this->reserved_stock = $stock->reserved_stock;
        $this->isModalOpen = true;
    }

    public function adjustStock(WarehouseStockService $stockService)
    {
        $this->validate([
            'adjustment_quantity' => 'required|integer|not_in:0',
            'adjustment_description' => 'required|string|max:500',
        ]);

        $stock = WarehouseStock::findOrFail($this->stockId);
        $quantity = (int) $this->adjustment_quantity;
        $description = 'Adjustment: ' . $this->adjustment_description;

        if ($quantity > 0) {
            $stockService->incrementStock($stock->warehouse_id, $stock->product_variant_id, $quantity, $stock->vendor_product_id, null, auth()->id(), $description);
        } else {
            $stockService->decrementStock($stock->warehouse_id, $stock->product_variant_id, abs($quantity), $stock->vendor_product_id, null, auth()->id(), $description);
        }

        $this->dispatch('notify', type: 'success', text: 'موجودی با موفقیت تعدیل شد.');
        $this->closeModal();
    }

    public function openAddProductModal()
    {
        $this->resetAddProductFields();
        $this->isAddProductModalOpen = true;
    }

    #[On('variant-selected')] // 💡 گوش دادن به رویداد از ProductVariantSelector
    public function setSelectedVariant($variantId)
    {
        $this->selectedVariantId = $variantId;
    }

    public function addProductToStock(WarehouseStockService $stockService)
    {
        $this->validate([
            'selectedVariantId' => 'required|exists:market_product_variants,id',
            'initialStock' => 'required|integer|min:0',
            'initialStockDescription' => 'required|string|max:500',
        ]);

        // 💡 بررسی اینکه آیا این واریانت قبلاً در این انبار موجود است یا خیر
        $existingStock = WarehouseStock::where('warehouse_id', $this->warehouseId)
                                       ->where('product_variant_id', $this->selectedVariantId)
                                       ->first();

        if ($existingStock) {
            $this->dispatch('notify', type: 'error', text: 'این محصول قبلاً در این انبار ثبت شده است. لطفاً موجودی آن را تعدیل کنید.');
            return;
        }

        $stockService->incrementStock(
            $this->warehouseId,
            $this->selectedVariantId,
            $this->initialStock,
            null, // vendor_product_id is null for now
            null,
            auth()->id(),
            $this->initialStockDescription
        );

        $this->dispatch('notify', type: 'success', text: 'محصول با موفقیت به انبار اضافه شد.');
        $this->closeModal();
    }

    #[On('close-modal')]
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isAddProductModalOpen = false;
        $this->resetInputFields();
        $this->resetAddProductFields();
    }

    private function resetInputFields()
    {
        $this->reset(['stockId', 'physical_stock', 'reserved_stock', 'adjustment_quantity', 'adjustment_description']);
        $this->resetValidation();
    }

    private function resetAddProductFields()
    {
        $this->reset(['selectedVariantId', 'initialStock']); // 💡 productSearch و productSearchResults حذف شدند
        $this->initialStockDescription = 'Initial stock count';
        $this->resetValidation();
    }

    public function render()
    {
        $stocks = WarehouseStock::with(['productVariant.masterProduct', 'vendorProduct.masterProduct', 'vendorProduct.vendor'])
            ->where('warehouse_id', $this->warehouseId)
            ->where(function ($query) {
                $query->whereHas('productVariant.masterProduct', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('vendorProduct.masterProduct', function ($q2) {
                    $q2->where('title', 'like', '%' . $this->search . '%');
                });
            })
            ->latest('updated_at')
            ->paginate(15);

        $warehouse = Warehouse::findOrFail($this->warehouseId);

        return view('market::livewire.admin.warehouse-stock-controller', [
            'stocks' => $stocks,
            'warehouse' => $warehouse,
        ]);
    }
}
