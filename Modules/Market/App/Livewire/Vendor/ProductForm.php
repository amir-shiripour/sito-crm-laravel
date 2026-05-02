<?php

namespace Modules\Market\App\Livewire\Vendor;

use Livewire\Component;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\VendorProduct;

class ProductForm extends Component
{
    public $searchQuery = '';
    public $master_product_id = '';
    public $selectedMasterProduct = null;

    public $available_variants = [];
    public $vendor_variants = [];

    public function mount()
    {
        if (request()->has('master_id')) {
            $this->selectProduct(request()->query('master_id'));
        }
    }

    public function getSearchResultsProperty()
    {
        if (strlen($this->searchQuery) < 2) return collect();

        return MasterProduct::where('status', 'active')
            ->where(function($q) {
                $q->where('title', 'like', "%{$this->searchQuery}%")
                    ->orWhere('crm_code', 'like', "%{$this->searchQuery}%");
            })
            ->with('category', 'brand')
            ->take(10)
            ->get();
    }

    public function selectProduct($id)
    {
        $this->master_product_id = $id;
        $this->selectedMasterProduct = MasterProduct::with('variants', 'category', 'brand')->find($id);
        $this->searchQuery = '';

        $this->loadVariants();
    }

    public function clearSelection()
    {
        $this->master_product_id = '';
        $this->selectedMasterProduct = null;
        $this->vendor_variants = [];
    }

    private function loadVariants()
    {
        $this->vendor_variants = [];
        $vendorId = auth()->user()->marketVendor->id;

        if ($this->selectedMasterProduct) {
            $this->available_variants = $this->selectedMasterProduct->variants->where('is_active', true);

            foreach ($this->available_variants as $variant) {
                $attributes = $variant->variant_attributes ?? [];
                $variantName = empty($attributes) ? 'تنوع استاندارد' : implode(' | ', (array)$attributes);

                $existing = VendorProduct::where('vendor_id', $vendorId)->where('product_variant_id', $variant->id)->first();

                $this->vendor_variants[$variant->id] = [
                    'display_name' => $variantName,
                    'sell_this' => $existing ? true : false,
                    'price' => $existing ? $existing->price : '',
                    'discount_price' => $existing ? $existing->discount_price : '',
                    'stock' => $existing ? $existing->stock : 0,
                    'reorder_point' => $existing ? $existing->reorder_point : 5,
                    'min_purchase' => $existing ? $existing->min_purchase_qty : 1,
                    'max_purchase' => $existing ? $existing->max_purchase_qty : '',
                    'is_active' => $existing ? in_array($existing->status, ['published', 'pending_review']) : true,
                ];
            }
        }
    }

    public function save()
    {
        $vendor = auth()->user()->marketVendor;
        $savedCount = 0;

        foreach ($this->vendor_variants as $variantId => $data) {

            if (!$data['sell_this']) {
                VendorProduct::where('vendor_id', $vendor->id)->where('product_variant_id', $variantId)->delete();
                continue;
            }

            if (!empty($data['price'])) {
                $cleanPrice = str_replace(',', '', $data['price']);
                $cleanDiscount = str_replace(',', '', $data['discount_price']);

                VendorProduct::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'product_variant_id' => $variantId,
                    ],
                    [
                        'price' => $cleanPrice,
                        'discount_price' => !empty($cleanDiscount) ? $cleanDiscount : null,
                        'stock' => $data['stock'] ?: 0,
                        'reorder_point' => $data['reorder_point'] ?? 5,
                        'min_purchase_qty' => $data['min_purchase'] ?: 1,
                        'max_purchase_qty' => !empty($data['max_purchase']) ? $data['max_purchase'] : null,
                        'status' => $data['is_active'] ? 'pending_review' : 'draft',
                    ]
                );
                $savedCount++;
            }
        }

        $this->dispatch('notify', type: 'success', text: "تغییرات انبار شما با موفقیت ذخیره شد.");
        return redirect()->route('user.market.vendor.products.index');
    }

    public function render()
    {
        return view('market::livewire.vendor.product-form');
    }
}
