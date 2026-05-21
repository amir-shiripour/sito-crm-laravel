<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\Entities\MasterProduct;

class ProductVariantSelector extends Component
{
    public $searchQuery = '';
    public $selectedMasterProduct = null;
    public $selectedVariantId = null;

    public function getSearchResultsProperty()
    {
        if (strlen($this->searchQuery) < 2) {
            return collect();
        }

        return MasterProduct::where('status', 'active')
            ->where(function ($q) {
                $q->where('title', 'like', "%{$this->searchQuery}%")
                  ->orWhere('crm_code', 'like', "%{$this->searchQuery}%");
            })
            ->with('category', 'brand')
            ->take(5)
            ->get();
    }

    public function selectProduct($id)
    {
        $this->selectedMasterProduct = MasterProduct::with('variants.masterProduct')->find($id);
        $this->searchQuery = '';
        $this->selectedVariantId = null;
    }

    public function clearSelection()
    {
        $this->selectedMasterProduct = null;
        $this->selectedVariantId = null;
    }

    public function updatedSelectedVariantId($variantId)
    {
        $this->dispatch('variant-selected', $variantId);
    }

    public function render()
    {
        return view('market::livewire.admin.product-variant-selector');
    }
}
