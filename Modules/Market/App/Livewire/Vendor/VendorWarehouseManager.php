<?php

namespace Modules\Market\App\Livewire\Vendor;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\Warehouse;
use Illuminate\Support\Facades\Auth;

class VendorWarehouseManager extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $vendorId = Auth::user()->marketVendor->id;

        $warehouses = Warehouse::where('vendor_id', $vendorId)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('market::livewire.vendor.vendor-warehouse-manager', [
            'warehouses' => $warehouses,
        ]);
    }
}
