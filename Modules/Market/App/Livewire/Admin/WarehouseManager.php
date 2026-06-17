<?php

namespace Modules\Market\App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\Warehouse;
use Livewire\Attributes\On;

class WarehouseManager extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = 'all'; // 'central', 'vendors', 'all'
    public $isModalOpen = false;
    public $warehouseId;
    public $name;
    public $code;
    public $is_active = true;
    public $vendor_id = null;

    public bool $isMultiVendor = false;

    public function mount()
    {
        $this->isMultiVendor = MarketSetting::getValue('system.store_type', 'multi') === 'multi';
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'vendor_id' => 'nullable|exists:market_vendors,id',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function setFilter($filter)
    {
        $this->filterType = $filter;
        $this->resetPage();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $this->warehouseId = $id;
        $this->name = $warehouse->name;
        $this->code = $warehouse->code;
        $this->is_active = $warehouse->is_active;
        $this->vendor_id = $warehouse->vendor_id;
        $this->isModalOpen = true;
    }

    public function store()
    {
        $validatedData = $this->validate();

        if (empty($validatedData['vendor_id'])) {
            $validatedData['vendor_id'] = null;
        }

        Warehouse::updateOrCreate(
            ['id' => $this->warehouseId],
            [
                'name' => $validatedData['name'],
                'is_active' => $validatedData['is_active'],
                'vendor_id' => $validatedData['vendor_id'],
            ]
        );

        $this->dispatch('notify', type: 'success', text: $this->warehouseId ? 'انبار با موفقیت ویرایش شد.' : 'انبار با موفقیت ایجاد شد.');
        $this->closeModal();
    }

    public function delete($id)
    {
        $warehouse = Warehouse::withCount('stocks')->findOrFail($id);
        if ($warehouse->stocks()->where('physical_stock', '>', 0)->exists()) {
            $this->dispatch('notify', type: 'error', text: 'این انبار دارای موجودی فیزیکی است و قابل حذف نیست.');
            return;
        }

        $warehouse->delete();
        $this->dispatch('notify', type: 'success', text: 'انبار با موفقیت حذف شد.');
    }

    #[On('close-modal')]
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->reset(['warehouseId', 'name', 'code', 'is_active', 'vendor_id']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Warehouse::with('vendor')
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('vendor', function ($q2) {
                      $q2->where('store_name', 'like', '%' . $this->search . '%');
                  });
            });

        if ($this->filterType === 'central') {
            $query->whereNull('vendor_id');
        } elseif ($this->filterType === 'vendors') {
            $query->whereNotNull('vendor_id');
        }

        $warehouses = $query->latest()->paginate(10);

        $vendors = collect();
        $adminVendor = null;

        if ($this->isMultiVendor) {
            $allActiveVendors = Vendor::with('user.roles')->where('status', 'active')->get();

            $adminVendor = $allActiveVendors->first(function ($vendor) {
                return $vendor->user && $vendor->user->hasAnyRole(['super-admin', 'admin']);
            });

            $vendors = $allActiveVendors->filter(function ($vendor) use ($adminVendor) {
                return !$adminVendor || $vendor->id !== $adminVendor->id;
            });
        }

        return view('market::livewire.admin.warehouse-manager', [
            'warehouses' => $warehouses,
            'vendors' => $vendors,
            'adminVendor' => $adminVendor,
        ]);
    }
}
