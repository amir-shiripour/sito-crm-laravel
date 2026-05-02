<?php

namespace Modules\Market\App\Livewire\Vendor;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\VendorProduct;

class ProductManager extends Component
{
    use WithPagination;

    public $editingId = null;
    public $editForm = [];

    public function edit($vendorProductId)
    {
        $vendorId = auth()->user()->marketVendor->id;
        $vp = VendorProduct::where('id', $vendorProductId)->where('vendor_id', $vendorId)->firstOrFail();

        $this->editingId = $vp->id;
        $this->editForm = [
            'price' => $vp->price,
            'discount_price' => $vp->discount_price,
            'stock' => $vp->stock,
            'reorder_point' => $vp->reorder_point,
            'min_purchase_qty' => $vp->min_purchase_qty,
            'max_purchase_qty' => $vp->max_purchase_qty,
            'is_active' => in_array($vp->status, ['published', 'pending_review']),
        ];
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->editForm = [];
    }

    public function saveEdit()
    {
        $this->validate([
            'editForm.price' => 'required|numeric',
            'editForm.stock' => 'required|numeric|min:0',
        ]);

        $vendorId = auth()->user()->marketVendor->id;
        $vp = VendorProduct::where('id', $this->editingId)->where('vendor_id', $vendorId)->firstOrFail();

        $vp->update([
            'price' => $this->editForm['price'],
            'discount_price' => !empty($this->editForm['discount_price']) ? $this->editForm['discount_price'] : null,
            'stock' => $this->editForm['stock'],
            'reorder_point' => $this->editForm['reorder_point'] ?: 5,
            'min_purchase_qty' => !empty($this->editForm['min_purchase_qty']) ? $this->editForm['min_purchase_qty'] : 1,
            'max_purchase_qty' => !empty($this->editForm['max_purchase_qty']) ? $this->editForm['max_purchase_qty'] : null,
            'status' => $this->editForm['is_active'] ? 'pending_review' : 'draft',
        ]);

        $this->editingId = null;
        $this->dispatch('notify', type: 'success', text: 'ویرایش تنوع با موفقیت انجام شد.');
    }

    public function delete($id)
    {
        $vendorId = auth()->user()->marketVendor->id;
        VendorProduct::where('id', $id)->where('vendor_id', $vendorId)->delete();
        $this->dispatch('notify', type: 'success', text: 'تنوع با موفقیت از انبار شما حذف شد.');
    }

    public function render()
    {
        $vendorId = auth()->user()->marketVendor->id;

        $masters = MasterProduct::whereHas('variants.vendorProducts', function($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })
            ->with(['variants' => function($q) use ($vendorId) {
                $q->whereHas('vendorProducts', function($q2) use ($vendorId) {
                    $q2->where('vendor_id', $vendorId);
                })->with(['vendorProducts' => function($q3) use ($vendorId) {
                    $q3->where('vendor_id', $vendorId);
                }]);
            }])
            ->latest()
            ->paginate(10);

        return view('market::livewire.vendor.product-manager', compact('masters'));
    }
}
