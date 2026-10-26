<?php

namespace Modules\FieldService\App\Livewire\Admin;

use App\Helpers\ProvinceCity;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Enums\TechnicianTier;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\TechnicianLifecycleService;

class TechniciansManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $tierFilter = 'all';

    // Direct Technician Registration Modal State
    public bool $showCreateModal = false;
    public string $firstName = '';
    public string $lastName = '';
    public string $mobile = '';
    public string $nationalCode = '';
    public string $shabaNumber = '';
    public string $province = 'مازندران';
    public string $city = 'بابل';
    public string $address = '';
    public int $operationalRadiusKm = 25;
    public string $tier = 'silver';
    public string $status = 'active';
    public array $selectedCategories = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset([
            'firstName',
            'lastName',
            'mobile',
            'nationalCode',
            'shabaNumber',
            'address',
            'selectedCategories',
        ]);
        $this->province = 'مازندران';
        $this->city = 'بابل';
        $this->operationalRadiusKm = 25;
        $this->tier = 'silver';
        $this->status = 'active';
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
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

    public function storeTechnician()
    {
        $this->validate([
            'firstName'           => 'required|string|max:100',
            'lastName'            => 'required|string|max:100',
            'mobile'              => 'required|string|regex:/^09[0-9]{9}$/',
            'nationalCode'        => 'nullable|string|size:10',
            'shabaNumber'         => 'nullable|string|max:34',
            'province'            => 'required|string',
            'city'                => 'required|string',
            'operationalRadiusKm' => 'required|integer|min:1|max:200',
            'tier'                => 'required|in:silver,gold,master',
            'status'              => 'required|in:active,pending_approval,suspended',
            'selectedCategories'  => 'nullable|array',
        ], [
            'firstName.required'           => 'وارد کردن نام متخصص الزامی است.',
            'lastName.required'            => 'وارد کردن نام خانوادگی الزامی است.',
            'mobile.required'              => 'شماره موبایل الزامی است.',
            'mobile.regex'                 => 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد (مثال: 09112110000).',
            'nationalCode.size'            => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'province.required'            => 'انتخاب استان الزامی است.',
            'city.required'                => 'انتخاب شهر الزامی است.',
            'operationalRadiusKm.required' => 'شعاع فعالیت را مشخص نمایید.',
        ]);

        $tech = TechnicianLifecycleService::createDirectTechnician([
            'first_name'            => $this->firstName,
            'last_name'             => $this->lastName,
            'mobile'                => $this->mobile,
            'national_code'         => $this->nationalCode,
            'shaba_number'          => $this->shabaNumber,
            'province'              => $this->province,
            'city'                  => $this->city,
            'address'               => $this->address,
            'operational_radius_km' => $this->operationalRadiusKm,
            'tier'                  => $this->tier,
            'status'                => $this->status,
            'categories'            => $this->selectedCategories,
        ]);

        $platform = \Modules\FieldService\App\Models\FieldServiceSetting::getPlatformName();
        $this->dispatch('notify', [
            'type' => 'success',
            'text' => "تکنسین {$tech->full_name} با موفقیت در ناوگان {$platform} ثبت و فعال گردید.",
        ]);

        $this->closeCreateModal();
        $this->resetPage();
    }

    public function toggleVerify(int $techId)
    {
        $tech = Technician::find($techId);
        if ($tech) {
            if ($tech->status === TechnicianStatus::ACTIVE) {
                $tech->status = TechnicianStatus::SUSPENDED;
                $tech->is_online = false;
                $msg = "تکنسین {$tech->full_name} تعلیق شد.";
            } else {
                $tech->status = TechnicianStatus::ACTIVE;
                $tech->verified_at = now();
                $msg = "مدارک و صلاحیت تکنسین {$tech->full_name} تایید و فعال شد.";

                // Also sync RegistrationRequest if exists
                if (!empty($tech->mobile) && class_exists(\App\Models\RegistrationRequest::class)) {
                    \App\Models\RegistrationRequest::where('mobile', $tech->mobile)
                        ->where('status', 'pending')
                        ->update([
                            'status'      => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                }
            }
            $tech->save();

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => $msg,
            ]);
        }
    }

    public function updateTier(int $techId, string $newTier)
    {
        $tech = Technician::find($techId);
        if ($tech) {
            $tech->tier = TechnicianTier::from($newTier);
            $tech->save();

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "سطح تکنسین {$tech->full_name} به {$tech->tier->label()} ارتقا یافت.",
            ]);
        }
    }

    public function render()
    {
        $query = Technician::query()->with(['categories', 'orders'])->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->tierFilter !== 'all') {
            $query->where('tier', $this->tierFilter);
        }

        $technicians = $query->paginate(12);

        $provinces = ProvinceCity::getProvinces();
        $cities = ProvinceCity::getCities($this->province);
        $allCategories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('fieldservice::livewire.admin.technicians-manager', [
            'technicians'   => $technicians,
            'provinces'     => $provinces,
            'cities'        => $cities,
            'allCategories' => $allCategories,
        ]);
    }
}
