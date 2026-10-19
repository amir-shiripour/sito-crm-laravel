<?php

namespace Modules\FieldService\App\Livewire\Admin;

use App\Helpers\ProvinceCity;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Clients\Entities\Client;
use Modules\FieldService\App\Models\VendorPartner;

class VendorsManager extends Component
{
    use WithPagination;

    // Search and Filters
    public string $search = '';
    public string $statusFilter = 'all';

    // Modal State
    public bool $showModal = false;
    public ?int $editingVendorId = null;

    // Client Selection / Quick Create
    public ?int $selectedClientId = null;
    public string $clientSearch = '';
    public bool $isCreatingNewClient = false;
    public string $newClientName = '';
    public string $newClientPhone = '';

    // Vendor Form Fields
    public string $store_name = '';
    public string $owner_name = '';
    public string $mobile = '';
    public string $phone = '';
    public string $license_number = '';
    public string $province = 'مازندران';
    public string $city = 'بابل';
    public string $address = '';
    public float $referral_commission_percent = 5.0;
    public string $status = 'active';
    public string $notes = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedProvince($value)
    {
        $cities = ProvinceCity::getCities($value);
        if ($value === 'مازندران' && in_array('بابل', $cities, true)) {
            $this->city = 'بابل';
        } else {
            $this->city = $cities[0] ?? '';
        }
    }

    public function selectClient(int $clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $this->selectedClientId = $client->id;
            $this->owner_name = $client->full_name;
            $this->mobile = $client->phone ?? '';
            $this->clientSearch = $client->full_name . ' (' . ($client->phone ?? 'بدون شماره') . ')';

            if (empty($this->store_name)) {
                $this->store_name = 'فروشگاه ' . $client->full_name;
            }

            // Fill address if client has one
            $defaultAddr = $client->addresses()->where('is_default', true)->first() ?: $client->addresses()->first();
            if ($defaultAddr) {
                if (!empty($defaultAddr->province)) {
                    $this->province = $defaultAddr->province;
                }
                if (!empty($defaultAddr->city)) {
                    $this->city = $defaultAddr->city;
                }
                if (!empty($defaultAddr->address)) {
                    $this->address = $defaultAddr->address;
                }
            }
        }
    }

    public function clearClientSelection()
    {
        $this->selectedClientId = null;
        $this->clientSearch = '';
    }

    public function openCreateModal()
    {
        $this->reset([
            'editingVendorId',
            'selectedClientId',
            'clientSearch',
            'isCreatingNewClient',
            'newClientName',
            'newClientPhone',
            'store_name',
            'owner_name',
            'mobile',
            'phone',
            'license_number',
            'address',
            'notes',
        ]);

        $this->province = 'مازندران';
        $this->city = 'بابل';
        $this->referral_commission_percent = 5.0;
        $this->status = 'active';
        $this->showModal = true;
    }

    public function editVendor(int $vendorId)
    {
        $vendor = VendorPartner::with('client')->findOrFail($vendorId);
        $this->editingVendorId = $vendor->id;
        $this->selectedClientId = $vendor->client_id;
        $this->clientSearch = $vendor->client ? ($vendor->client->full_name . ' (' . ($vendor->client->phone ?? '') . ')') : '';
        $this->isCreatingNewClient = false;

        $this->store_name = $vendor->store_name;
        $this->owner_name = $vendor->owner_name;
        $this->mobile = $vendor->mobile;
        $this->phone = $vendor->phone ?? '';
        $this->license_number = $vendor->license_number ?? '';
        $this->province = $vendor->province ?? 'مازندران';
        $this->city = $vendor->city ?? 'بابل';
        $this->address = $vendor->address ?? '';
        $this->referral_commission_percent = (float) $vendor->referral_commission_percent;
        $this->status = $vendor->status ?? 'active';
        $this->notes = $vendor->notes ?? '';

        $this->showModal = true;
    }

    public function saveVendor()
    {
        $this->validate([
            'store_name'                  => 'required|string|max:120',
            'owner_name'                  => 'required|string|max:100',
            'mobile'                      => 'required|string|max:20',
            'province'                    => 'required|string|max:60',
            'city'                        => 'required|string|max:60',
            'referral_commission_percent' => 'required|numeric|min:0|max:100',
            'status'                      => 'required|in:active,inactive,suspended',
        ]);

        // Handle client association
        if ($this->isCreatingNewClient && !empty($this->newClientName) && !empty($this->newClientPhone)) {
            $client = Client::create([
                'full_name'  => $this->newClientName,
                'phone'      => $this->newClientPhone,
                'created_by' => auth()->id(),
            ]);
            $this->selectedClientId = $client->id;
        } elseif (!$this->selectedClientId && !empty($this->mobile)) {
            // Find existing client or auto-create CRM client for partner
            $client = Client::firstOrCreate(
                ['phone' => $this->mobile],
                [
                    'full_name'  => $this->owner_name,
                    'created_by' => auth()->id(),
                ]
            );
            $this->selectedClientId = $client->id;
        }

        $data = [
            'client_id'                   => $this->selectedClientId,
            'store_name'                  => $this->store_name,
            'owner_name'                  => $this->owner_name,
            'mobile'                      => $this->mobile,
            'phone'                       => $this->phone,
            'license_number'              => $this->license_number,
            'province'                    => $this->province,
            'city'                        => $this->city,
            'address'                     => $this->address,
            'referral_commission_percent' => $this->referral_commission_percent,
            'status'                      => $this->status,
            'notes'                       => $this->notes,
        ];

        if ($this->editingVendorId) {
            $vendor = VendorPartner::findOrFail($this->editingVendorId);
            $vendor->update($data);
            $message = "اطلاعات فروشگاه همکار «{$vendor->store_name}» به‌روزرسانی شد.";
        } else {
            $vendor = VendorPartner::create($data);
            $message = "فروشگاه همکار «{$vendor->store_name}» با کد معرف {$vendor->referral_code} ثبت گردید.";
        }

        $this->showModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'text' => $message,
        ]);
    }

    public function toggleStatus(int $vendorId)
    {
        $vendor = VendorPartner::findOrFail($vendorId);
        $vendor->status = $vendor->status === 'active' ? 'inactive' : 'active';
        $vendor->save();

        $this->dispatch('notify', [
            'type' => 'info',
            'text' => "وضعیت فروشگاه به " . ($vendor->status === 'active' ? 'فعال' : 'غیرفعال') . " تغییر یافت.",
        ]);
    }

    public function deleteVendor(int $vendorId)
    {
        $vendor = VendorPartner::findOrFail($vendorId);
        $vendor->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => "فروشگاه همکار با موفقیت حذف گردید.",
        ]);
    }

    public function render()
    {
        $query = VendorPartner::query()
            ->with(['client', 'orders'])
            ->withCount('orders')
            ->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('store_name', 'like', "%{$this->search}%")
                  ->orWhere('owner_name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%")
                  ->orWhere('referral_code', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $vendors = $query->paginate(12);

        // Client search suggestions for modal
        $clientSuggestions = collect();
        if (strlen($this->clientSearch) >= 2 && !$this->selectedClientId) {
            $clientSuggestions = Client::query()
                ->where('full_name', 'like', "%{$this->clientSearch}%")
                ->orWhere('phone', 'like', "%{$this->clientSearch}%")
                ->limit(6)
                ->get();
        }

        // Stats summary
        $totalVendors = VendorPartner::count();
        $activeVendors = VendorPartner::where('status', 'active')->count();
        $totalReferredOrders = \Modules\FieldService\App\Models\ServiceOrder::whereNotNull('vendor_id')->count();

        $provinces = ProvinceCity::getProvinces();
        $cities = ProvinceCity::getCities($this->province);

        return view('fieldservice::livewire.admin.vendors-manager', [
            'vendors'             => $vendors,
            'clientSuggestions'   => $clientSuggestions,
            'provinces'           => $provinces,
            'cities'              => $cities,
            'totalVendors'        => $totalVendors,
            'activeVendors'       => $activeVendors,
            'totalReferredOrders' => $totalReferredOrders,
        ]);
    }
}
