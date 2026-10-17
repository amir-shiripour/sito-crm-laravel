<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\FieldService\App\Models\VendorPartner;

class VendorsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;

    public string $store_name = '';
    public string $owner_name = '';
    public string $mobile = '';
    public string $city = 'بابل';
    public string $address = '';
    public float $referral_commission_percent = 5.0;

    public function openCreateModal()
    {
        $this->reset(['store_name', 'owner_name', 'mobile', 'address']);
        $this->showModal = true;
    }

    public function saveVendor()
    {
        $this->validate([
            'store_name' => 'required|string|max:120',
            'owner_name' => 'required|string|max:100',
            'mobile'     => 'required|string|max:20',
            'city'       => 'required|string|max:60',
        ]);

        VendorPartner::create([
            'store_name'                  => $this->store_name,
            'owner_name'                  => $this->owner_name,
            'mobile'                      => $this->mobile,
            'city'                        => $this->city,
            'address'                     => $this->address,
            'referral_commission_percent' => $this->referral_commission_percent,
            'status'                      => 'active',
        ]);

        $this->showModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'text' => "فروشگاه همکار {$this->store_name} با موفقیت ثبت گردید.",
        ]);
    }

    public function render()
    {
        $query = VendorPartner::query()->with('orders')->latest();

        if (!empty($this->search)) {
            $query->where('store_name', 'like', "%{$this->search}%")
                  ->orWhere('owner_name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%");
        }

        $vendors = $query->paginate(10);

        return view('fieldservice::livewire.admin.vendors-manager', [
            'vendors' => $vendors,
        ]);
    }
}
