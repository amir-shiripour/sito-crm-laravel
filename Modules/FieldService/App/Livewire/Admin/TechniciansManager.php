<?php

namespace Modules\FieldService\App\Livewire\Admin;

use App\Helpers\ProvinceCity;
use App\Models\User;
use App\Models\UserCustomValue;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Enums\TechnicianTier;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\FieldServiceSetting;
use Modules\FieldService\App\Models\Section;
use Modules\FieldService\App\Models\ServiceGroup;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Models\TechnicianSkill;
use Modules\FieldService\App\Services\TechnicianLifecycleService;

class TechniciansManager extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $tierFilter = 'all';
    public string $sectionFilter = 'all';
    public string $groupFilter = 'all';

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
    public ?int $createActiveSectionId = null;
    public $nationalCardFile = null;

    // Create Modal Contract Options
    public bool $autoIssueContract = true;
    public ?int $createTemplateId = null;
    public string $createContractStatus = 'active';

    // Edit Technician Modal State
    public bool $showEditModal = false;
    public ?int $editTechnicianId = null;
    public string $editFirstName = '';
    public string $editLastName = '';
    public string $editMobile = '';
    public string $editNationalCode = '';
    public string $editShabaNumber = '';
    public string $editProvince = 'مازندران';
    public string $editCity = 'بابل';
    public string $editAddress = '';
    public int $editOperationalRadiusKm = 25;
    public string $editTier = 'silver';
    public string $editStatus = 'active';
    public array $editSelectedCategories = [];
    public ?int $editActiveSectionId = null;
    public ?float $editLat = null;
    public ?float $editLng = null;
    public $editNationalCardFile = null;
    public ?string $existingNationalCardPath = null;

    // Dossier / Details Modal State
    public bool $showDossierModal = false;
    public ?int $dossierTechnicianId = null;

    // ContractForge Manual Issuance Modal State
    public bool $showContractModal = false;
    public ?int $contractTechnicianId = null;
    public string $contractTechnicianName = '';
    public ?int $selectedTemplateId = null;
    public string $contractInitialStatus = 'active';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSectionFilter(): void
    {
        $this->groupFilter = 'all';
        $this->resetPage();
    }

    public function updatedGroupFilter(): void
    {
        $this->resetPage();
    }

    public static function getCityCoordinates(string $province, string $city): array
    {
        $coords = [
            'بابل'         => ['lat' => 36.5444, 'lng' => 52.6780],
            'ساری'         => ['lat' => 36.5659, 'lng' => 53.0586],
            'قائم‌شهر'     => ['lat' => 36.4647, 'lng' => 52.8598],
            'آمل'          => ['lat' => 36.4676, 'lng' => 52.3507],
            'بابلسر'       => ['lat' => 36.7020, 'lng' => 52.6570],
            'چالوس'        => ['lat' => 36.6550, 'lng' => 51.4204],
            'نوشهر'        => ['lat' => 36.6492, 'lng' => 51.4960],
            'تنکابن'       => ['lat' => 36.8163, 'lng' => 50.8738],
            'رامسر'        => ['lat' => 36.9174, 'lng' => 50.6477],
            'نور'          => ['lat' => 36.5714, 'lng' => 52.0142],
            'محمودآباد'    => ['lat' => 36.6319, 'lng' => 52.2629],
            'بهشهر'        => ['lat' => 36.6980, 'lng' => 53.5510],
            'نکا'          => ['lat' => 36.6508, 'lng' => 53.2990],
            'فریدونکنار'   => ['lat' => 36.6853, 'lng' => 52.5186],
            'سوادکوه'      => ['lat' => 36.0833, 'lng' => 52.9333],
            'سوادکوه شمالی'=> ['lat' => 36.2917, 'lng' => 52.8750],
            'جویبار'       => ['lat' => 36.6410, 'lng' => 52.9122],
            'گلوگاه'       => ['lat' => 36.7289, 'lng' => 53.8097],
            'میاندرود'     => ['lat' => 36.6500, 'lng' => 53.2000],
            'کلاردشت'      => ['lat' => 36.4950, 'lng' => 51.1560],
            'عباس‌آباد'     => ['lat' => 36.7170, 'lng' => 51.1070],
        ];

        if (isset($coords[$city])) {
            return $coords[$city];
        }

        return [
            'lat' => (float) config('fieldservice.map.default_lat', 36.5444),
            'lng' => (float) config('fieldservice.map.default_lng', 52.6780),
        ];
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
            'nationalCardFile',
        ]);
        $this->province = 'مازندران';
        $this->city = 'بابل';
        $this->operationalRadiusKm = (int) FieldServiceSetting::get('default_technician_radius_km', 25);
        $this->tier = 'silver';
        $this->status = 'active';
        $this->createActiveSectionId = Section::where('is_active', true)->orderBy('sort_order')->value('id');

        if ($this->isContractForgeActive()) {
            $this->autoIssueContract = true;
            $this->createTemplateId = \Modules\ContractForge\App\Models\ContractTemplate::where('entity_type', 'field_service_technician')
                ->where('is_active', true)
                ->orderBy('id')
                ->value('id');
            $this->createContractStatus = 'active';
        } else {
            $this->autoIssueContract = false;
            $this->createTemplateId = null;
        }

        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->nationalCardFile = null;
    }

    public function setCreateActiveSection(int $sectionId): void
    {
        $this->createActiveSectionId = $sectionId;
    }

    public function toggleCreateGroup(int $groupId): void
    {
        $serviceIds = Category::where('group_id', $groupId)->where('is_active', true)->pluck('id')->toArray();
        $intSelected = array_map('intval', $this->selectedCategories);
        $allSelected = !empty($serviceIds) && empty(array_diff($serviceIds, $intSelected));

        if ($allSelected) {
            $this->selectedCategories = array_values(array_diff($intSelected, $serviceIds));
        } else {
            $this->selectedCategories = array_values(array_unique(array_merge($intSelected, $serviceIds)));
        }
    }

    public function toggleCreateSection(int $sectionId): void
    {
        $serviceIds = Category::where('section_id', $sectionId)->where('is_active', true)->pluck('id')->toArray();
        $intSelected = array_map('intval', $this->selectedCategories);
        $allSelected = !empty($serviceIds) && empty(array_diff($serviceIds, $intSelected));

        if ($allSelected) {
            $this->selectedCategories = array_values(array_diff($intSelected, $serviceIds));
        } else {
            $this->selectedCategories = array_values(array_unique(array_merge($intSelected, $serviceIds)));
        }
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
            'nationalCardFile'    => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ], [
            'firstName.required'           => 'وارد کردن نام متخصص الزامی است.',
            'lastName.required'            => 'وارد کردن نام خانوادگی الزامی است.',
            'mobile.required'              => 'شماره موبایل الزامی است.',
            'mobile.regex'                 => 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد (مثال: 09112110000).',
            'nationalCode.size'            => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'province.required'            => 'انتخاب استان الزامی است.',
            'city.required'                => 'انتخاب شهر الزامی است.',
            'operationalRadiusKm.required' => 'شعاع فعالیت را مشخص نمایید.',
            'nationalCardFile.mimes'       => 'فرمت فایل مدرک شناسایی باید JPG، PNG، WEBP یا PDF باشد.',
            'nationalCardFile.max'         => 'حجم فایل مدرک حداکثر ۵ مگابایت است.',
        ]);

        $coords = self::getCityCoordinates($this->province, $this->city);

        $uploadedPath = null;
        if ($this->nationalCardFile) {
            $uploadedPath = $this->nationalCardFile->store('uploads/custom_fields', 'public');
        }

        $meta = [];
        if ($uploadedPath) {
            $meta['national_card_image'] = $uploadedPath;
            $meta['custom_fields'] = ['national_card_image' => $uploadedPath];
            $meta['verification_submitted'] = true;
        }

        $cleanShaba = $this->shabaNumber ? trim($this->shabaNumber) : '';
        if (!empty($cleanShaba)) {
            $numeric = preg_replace('/[^0-9]/', '', $cleanShaba);
            $cleanShaba = $numeric !== '' ? ('IR' . $numeric) : '';
        }

        $tech = TechnicianLifecycleService::createDirectTechnician([
            'first_name'            => $this->firstName,
            'last_name'             => $this->lastName,
            'mobile'                => $this->mobile,
            'national_code'         => $this->nationalCode,
            'shaba_number'          => $cleanShaba,
            'province'              => $this->province,
            'city'                  => $this->city,
            'address'               => $this->address,
            'lat'                   => $coords['lat'],
            'lng'                   => $coords['lng'],
            'operational_radius_km' => $this->operationalRadiusKm,
            'tier'                  => $this->tier,
            'status'                => $this->status,
            'categories'            => $this->selectedCategories,
            'national_card_image'   => $uploadedPath,
            'meta'                  => $meta,
        ]);

        // Sync to UserCustomValue if user exists
        if ($tech->user_id && $uploadedPath) {
            UserCustomValue::updateOrCreate(
                ['user_id' => $tech->user_id, 'field_name' => 'national_card_image'],
                ['value' => $uploadedPath]
            );
        }

        // ContractForge Issuance
        $contractIssued = false;
        if ($this->autoIssueContract && $this->isContractForgeActive() && $this->createTemplateId) {
            try {
                $template = \Modules\ContractForge\App\Models\ContractTemplate::find($this->createTemplateId);
                if ($template) {
                    \Modules\ContractForge\Services\ContractEngine::generate(
                        $template,
                        $tech,
                        null,
                        auth()->id(),
                        $this->createContractStatus
                    );
                    $contractIssued = true;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('ContractForge auto issue on create technician: ' . $e->getMessage());
            }
        } elseif ($tech->status === TechnicianStatus::ACTIVE) {
            TechnicianLifecycleService::triggerApprovalContract($tech);
        }

        $platform = FieldServiceSetting::getPlatformName();
        $msg = "تکنسین {$tech->full_name} با موفقیت در ناوگان {$platform} ثبت گردید.";
        if ($contractIssued) {
            $msg .= ' قرارداد همکاری نیز صادر و آماده امضا شد.';
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => $msg,
        ]);

        $this->closeCreateModal();
        $this->resetPage();
    }

    public function openEditModal(int $id)
    {
        $tech = Technician::with(['categories.group.section', 'categories.section', 'user'])->findOrFail($id);
        $this->editTechnicianId = $tech->id;
        $this->editFirstName = (string) ($tech->first_name ?? '');
        $this->editLastName = (string) ($tech->last_name ?? '');
        $this->editMobile = (string) ($tech->mobile ?? '');
        $this->editNationalCode = (string) ($tech->national_code ?? '');
        $this->editShabaNumber = (string) ($tech->shaba_number ?? '');
        $this->editProvince = (string) ($tech->province ?: 'مازندران');
        $this->editCity = (string) ($tech->city ?: 'بابل');
        $this->editAddress = (string) ($tech->address ?? '');
        $this->editOperationalRadiusKm = (int) ($tech->operational_radius_km ?? 25);
        $this->editTier = $tech->tier?->value ?? 'silver';
        $this->editStatus = $tech->status?->value ?? 'active';
        $this->editLat = $tech->lat;
        $this->editLng = $tech->lng;
        $this->editSelectedCategories = $tech->categories->pluck('id')->map(fn($v) => (int)$v)->toArray();
        $this->editActiveSectionId = Section::where('is_active', true)->orderBy('sort_order')->value('id');
        $this->editNationalCardFile = null;

        $meta = is_array($tech->meta) ? $tech->meta : [];
        $this->existingNationalCardPath = $meta['national_card_image'] ?? $meta['custom_fields']['national_card_image'] ?? null;

        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editTechnicianId = null;
        $this->editNationalCardFile = null;
    }

    public function setEditActiveSection(int $sectionId): void
    {
        $this->editActiveSectionId = $sectionId;
    }

    public function toggleEditGroup(int $groupId): void
    {
        $serviceIds = Category::where('group_id', $groupId)->where('is_active', true)->pluck('id')->toArray();
        $intSelected = array_map('intval', $this->editSelectedCategories);
        $allSelected = !empty($serviceIds) && empty(array_diff($serviceIds, $intSelected));

        if ($allSelected) {
            $this->editSelectedCategories = array_values(array_diff($intSelected, $serviceIds));
        } else {
            $this->editSelectedCategories = array_values(array_unique(array_merge($intSelected, $serviceIds)));
        }
    }

    public function toggleEditSection(int $sectionId): void
    {
        $serviceIds = Category::where('section_id', $sectionId)->where('is_active', true)->pluck('id')->toArray();
        $intSelected = array_map('intval', $this->editSelectedCategories);
        $allSelected = !empty($serviceIds) && empty(array_diff($serviceIds, $intSelected));

        if ($allSelected) {
            $this->editSelectedCategories = array_values(array_diff($intSelected, $serviceIds));
        } else {
            $this->editSelectedCategories = array_values(array_unique(array_merge($intSelected, $serviceIds)));
        }
    }

    public function updatedEditProvince($value)
    {
        $cities = ProvinceCity::getCities($value);
        if ($value === 'مازندران' && in_array('بابل', $cities, true)) {
            $this->editCity = 'بابل';
        } else {
            $this->editCity = $cities[0] ?? '';
        }
    }

    public function updateTechnician()
    {
        if (!$this->editTechnicianId) {
            return;
        }

        $this->validate([
            'editFirstName'           => 'required|string|max:100',
            'editLastName'            => 'required|string|max:100',
            'editMobile'              => 'required|string|regex:/^09[0-9]{9}$/',
            'editNationalCode'        => 'nullable|string|size:10',
            'editShabaNumber'         => 'nullable|string|max:34',
            'editProvince'            => 'required|string',
            'editCity'                => 'required|string',
            'editOperationalRadiusKm' => 'required|integer|min:1|max:200',
            'editTier'                => 'required|in:silver,gold,master',
            'editStatus'              => 'required|in:active,pending_approval,suspended',
            'editSelectedCategories'  => 'nullable|array',
            'editNationalCardFile'    => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ], [
            'editFirstName.required'           => 'نام متخصص الزامی است.',
            'editLastName.required'            => 'نام خانوادگی الزامی است.',
            'editMobile.required'              => 'شماره موبایل الزامی است.',
            'editMobile.regex'                 => 'شماره موبایل نامعتبر است.',
            'editNationalCode.size'            => 'کد ملی باید ۱۰ رقم باشد.',
            'editProvince.required'            => 'استان الزامی است.',
            'editCity.required'                => 'شهر الزامی است.',
            'editOperationalRadiusKm.required' => 'شعاع عملیاتی الزامی است.',
        ]);

        $tech = Technician::findOrFail($this->editTechnicianId);
        $prevStatus = $tech->status->value;

        $cleanShaba = $this->editShabaNumber ? trim($this->editShabaNumber) : '';
        if (!empty($cleanShaba)) {
            $numeric = preg_replace('/[^0-9]/', '', $cleanShaba);
            $cleanShaba = $numeric !== '' ? ('IR' . $numeric) : '';
        }

        $tech->first_name = $this->editFirstName;
        $tech->last_name = $this->editLastName;
        $tech->mobile = $this->editMobile;
        $tech->national_code = $this->editNationalCode;
        $tech->shaba_number = $cleanShaba;
        $tech->province = $this->editProvince;
        $tech->city = $this->editCity;
        $tech->address = $this->editAddress;
        $tech->operational_radius_km = $this->editOperationalRadiusKm;
        $tech->tier = TechnicianTier::from($this->editTier);
        $tech->status = TechnicianStatus::from($this->editStatus);

        if ($tech->status === TechnicianStatus::ACTIVE && !$tech->verified_at) {
            $tech->verified_at = now();
        }

        // Set or update coordinates if missing or city changed
        if (empty($tech->lat) || empty($tech->lng)) {
            $coords = self::getCityCoordinates($this->editProvince, $this->editCity);
            $tech->lat = $coords['lat'];
            $tech->lng = $coords['lng'];
        }

        // Handle national card file update
        if ($this->editNationalCardFile) {
            $path = $this->editNationalCardFile->store('uploads/custom_fields', 'public');
            $meta = is_array($tech->meta) ? $tech->meta : [];
            $meta['national_card_image'] = $path;
            $meta['custom_fields']['national_card_image'] = $path;
            $meta['verification_submitted'] = true;
            $tech->meta = $meta;

            if ($tech->user_id) {
                UserCustomValue::updateOrCreate(
                    ['user_id' => $tech->user_id, 'field_name' => 'national_card_image'],
                    ['value' => $path]
                );
            }
        }

        $tech->save();

        // Update linked user
        if ($tech->user) {
            $tech->user->update([
                'name'   => $tech->full_name,
                'mobile' => $tech->mobile,
            ]);
        }

        // Sync Categories
        $selectedIds = array_values(array_unique(array_filter(array_map('intval', $this->editSelectedCategories))));
        $tech->skills()->whereNotIn('category_id', $selectedIds)->delete();
        foreach ($selectedIds as $catId) {
            if (Category::where('id', $catId)->exists()) {
                TechnicianSkill::firstOrCreate([
                    'technician_id' => $tech->id,
                    'category_id'   => $catId,
                ], [
                    'is_verified'      => ($tech->status === TechnicianStatus::ACTIVE),
                    'experience_years' => 1,
                ]);
            }
        }

        // Trigger Contract rule if newly activated
        if ($tech->status === TechnicianStatus::ACTIVE && $prevStatus !== 'active') {
            TechnicianLifecycleService::triggerApprovalContract($tech, $prevStatus);
        }

        $this->closeEditModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => "اطلاعات تکنسین {$tech->full_name} با موفقیت به‌روزرسانی شد.",
        ]);
    }

    public function openDossierModal(int $id)
    {
        $this->dossierTechnicianId = $id;
        $this->showDossierModal = true;
    }

    public function closeDossierModal()
    {
        $this->showDossierModal = false;
        $this->dossierTechnicianId = null;
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
                $tech->skills()->update(['is_verified' => true]);
                $msg = "مدارک و صلاحیت تکنسین {$tech->full_name} تایید و فعال شد.";

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

            if ($tech->status === TechnicianStatus::ACTIVE) {
                TechnicianLifecycleService::triggerApprovalContract($tech);
            }

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

            if ($tech->status === TechnicianStatus::ACTIVE) {
                TechnicianLifecycleService::triggerApprovalContract($tech);
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "سطح تکنسین {$tech->full_name} به {$tech->tier->label()} ارتقا یافت.",
            ]);
        }
    }

    protected function isContractForgeActive(): bool
    {
        return class_exists(\Modules\ContractForge\Services\ContractModuleHelper::class)
            && class_exists(\Modules\ContractForge\Services\ContractEngine::class)
            && \Modules\ContractForge\Services\ContractModuleHelper::isModuleActive('ContractForge');
    }

    public function openContractModal(int $techId): void
    {
        if (! $this->isContractForgeActive()) {
            return;
        }

        $tech = Technician::findOrFail($techId);
        $this->contractTechnicianId = $tech->id;
        $this->contractTechnicianName = $tech->full_name;
        $this->selectedTemplateId = \Modules\ContractForge\App\Models\ContractTemplate::where('entity_type', 'field_service_technician')
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');
        $this->contractInitialStatus = 'active';
        $this->resetErrorBag();
        $this->showContractModal = true;
    }

    public function closeContractModal(): void
    {
        $this->showContractModal = false;
        $this->contractTechnicianId = null;
        $this->contractTechnicianName = '';
        $this->selectedTemplateId = null;
    }

    public function issueContract(): void
    {
        if (! $this->isContractForgeActive() || ! $this->contractTechnicianId) {
            return;
        }

        $this->validate([
            'selectedTemplateId'    => 'required|integer|exists:contract_templates,id',
            'contractInitialStatus' => 'required|in:active,draft',
        ], [
            'selectedTemplateId.required' => 'انتخاب قالب قرارداد الزامی است.',
        ]);

        $tech = Technician::findOrFail($this->contractTechnicianId);
        $template = \Modules\ContractForge\App\Models\ContractTemplate::findOrFail($this->selectedTemplateId);

        $contract = \Modules\ContractForge\Services\ContractEngine::generate(
            $template,
            $tech,
            null,
            auth()->id(),
            $this->contractInitialStatus
        );

        $this->closeContractModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => "قرارداد {$contract->contract_number} برای {$tech->full_name} صادر شد.",
        ]);
    }

    public function render()
    {
        $contractForgeActive = $this->isContractForgeActive();
        $withRelations = [
            'categories.group.section',
            'categories.section',
            'orders',
            'user',
        ];
        if ($contractForgeActive) {
            $withRelations[] = 'contracts';
        }

        $query = Technician::query()->with($withRelations)->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%")
                  ->orWhere('national_code', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->tierFilter !== 'all') {
            $query->where('tier', $this->tierFilter);
        }

        if ($this->sectionFilter !== 'all') {
            $secId = (int) $this->sectionFilter;
            $query->whereHas('categories', function ($q) use ($secId) {
                $q->where('section_id', $secId)
                  ->orWhereHas('group', fn($g) => $g->where('section_id', $secId));
            });
        }

        if ($this->groupFilter !== 'all') {
            $grpId = (int) $this->groupFilter;
            $query->whereHas('categories', function ($q) use ($grpId) {
                $q->where('group_id', $grpId);
            });
        }

        $technicians = $query->paginate(12);

        $provinces = ProvinceCity::getProvinces();
        $cities = ProvinceCity::getCities($this->province);
        $editCities = ProvinceCity::getCities($this->editProvince);

        // Sections with 3-tier hierarchy (Section -> Groups -> Services)
        $sections = Section::with([
            'groups' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')->with([
                'services' => fn($sq) => $sq->where('is_active', true)->orderBy('sort_order')
            ]),
            'services' => fn($q) => $q->where('is_active', true)->whereNull('group_id')->orderBy('sort_order')
        ])->where('is_active', true)->orderBy('sort_order')->get();

        $filterGroups = collect();
        if ($this->sectionFilter !== 'all') {
            $secId = (int) $this->sectionFilter;
            $filterGroups = ServiceGroup::where('is_active', true)
                ->where(function ($q) use ($secId) {
                    $q->where('section_id', $secId)
                      ->orWhereHas('services', fn ($sq) => $sq->where('section_id', $secId));
                })
                ->orderBy('sort_order')
                ->get();
        } else {
            $filterGroups = ServiceGroup::where('is_active', true)->orderBy('sort_order')->get();
        }

        $technicianTemplates = $contractForgeActive
            ? \Modules\ContractForge\App\Models\ContractTemplate::where('entity_type', 'field_service_technician')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect();

        // Summary Statistics
        $totalCount = Technician::count();
        $activeCount = Technician::where('status', TechnicianStatus::ACTIVE)->count();
        $onlineCount = Technician::where('is_online', true)->count();
        $vipCount = Technician::whereIn('tier', [TechnicianTier::GOLD, TechnicianTier::MASTER])->count();
        $pendingContractCount = $contractForgeActive
            ? Technician::whereHas('contracts', fn($q) => $q->where('status', 'active'))->count()
            : 0;

        $defaultRadius = (int) FieldServiceSetting::get('default_technician_radius_km', 25);

        // Load Dossier data if modal open
        $dossierTech = null;
        if ($this->showDossierModal && $this->dossierTechnicianId) {
            $dossierTech = Technician::with([
                'user',
                'categories.group.section',
                'categories.section',
                'orders' => fn($q) => $q->latest()->take(6),
                'contracts' => fn($q) => $q->latest(),
            ])->find($this->dossierTechnicianId);
        }

        return view('fieldservice::livewire.admin.technicians-manager', [
            'technicians'          => $technicians,
            'provinces'            => $provinces,
            'cities'               => $cities,
            'editCities'           => $editCities,
            'sections'             => $sections,
            'filterGroups'         => $filterGroups,
            'contractForgeActive'  => $contractForgeActive,
            'technicianTemplates'  => $technicianTemplates,
            'totalCount'           => $totalCount,
            'activeCount'          => $activeCount,
            'onlineCount'          => $onlineCount,
            'vipCount'             => $vipCount,
            'pendingContractCount' => $pendingContractCount,
            'defaultRadius'        => $defaultRadius,
            'dossierTech'          => $dossierTech,
            'showCreateModal'      => $this->showCreateModal,
            'showEditModal'        => $this->showEditModal,
            'showDossierModal'     => $this->showDossierModal,
            'showContractModal'    => $this->showContractModal,
        ]);
    }
}
