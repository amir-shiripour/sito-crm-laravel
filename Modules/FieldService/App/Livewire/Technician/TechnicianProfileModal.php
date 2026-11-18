<?php

namespace Modules\FieldService\App\Livewire\Technician;

use App\Helpers\ProvinceCity;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Models\TechnicianSkill;
use Modules\FieldService\App\Support\TechnicianCustomFieldsHelper;

class TechnicianProfileModal extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;
    public string $firstName = '';
    public string $lastName = '';
    public string $province = 'مازندران';
    public string $city = 'بابل';
    public array $customFields = [];
    public array $customFiles = [];
    public array $existingFiles = [];
    public array $selectedCategories = [];
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    protected $listeners = [
        'open-technician-profile' => 'openModal',
    ];

    public function mount(): void
    {
        $this->loadTechnicianData();
    }

    public function openModal(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->customFiles = [];
        $this->loadTechnicianData();
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    protected function loadTechnicianData(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $technician = Technician::where('user_id', $user->id)->first();
        if (!$technician) {
            return;
        }

        $this->firstName = (string) ($technician->first_name ?? '');
        $this->lastName = (string) ($technician->last_name ?? '');
        $this->selectedCategories = $technician->categories()
            ->pluck('field_service_categories.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $profileFields = TechnicianCustomFieldsHelper::getFields('profile');
        $loaded = TechnicianCustomFieldsHelper::loadExistingValues($user, $technician, $profileFields);

        $this->customFields = [];
        $this->existingFiles = [];

        foreach ($profileFields as $field) {
            $name = $field->field_name;
            $type = strtolower($field->field_type ?? 'text');

            if ($type === 'file') {
                $this->existingFiles[$name] = !empty($loaded[$name]) ? (string) $loaded[$name] : null;
            } else {
                $this->customFields[$name] = $loaded[$name] ?? '';
            }
        }

        $this->province = !empty($this->customFields['province'])
            ? (string) $this->customFields['province']
            : ($technician->province ?: 'مازندران');

        $this->city = !empty($this->customFields['city'])
            ? (string) $this->customFields['city']
            : ($technician->city ?: 'بابل');

        if (array_key_exists('province', $this->customFields) && empty($this->customFields['province'])) {
            $this->customFields['province'] = $this->province;
        }
        if (array_key_exists('city', $this->customFields) && empty($this->customFields['city'])) {
            $this->customFields['city'] = $this->city;
        }
    }

    public function updatedCustomFields($value, $key): void
    {
        if ($key === 'province') {
            $this->province = (string) $value;
            $cities = ProvinceCity::getCities($this->province);
            if ($this->province === 'مازندران' && in_array('بابل', $cities, true)) {
                $this->city = 'بابل';
            } else {
                $this->city = $cities[0] ?? '';
            }
            $this->customFields['city'] = $this->city;
        } elseif ($key === 'city') {
            $this->city = (string) $value;
        }
    }

    public function saveProfile(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $user = Auth::user();
        $technician = $user ? Technician::where('user_id', $user->id)->first() : null;

        if (!$user || !$technician) {
            $this->errorMessage = 'حساب کاربری تکنسین یافت نشد.';
            return;
        }

        $profileFields = TechnicianCustomFieldsHelper::getFields('profile');
        $dynamic = TechnicianCustomFieldsHelper::buildValidation($profileFields, $this->existingFiles, false);

        $rules = array_merge([
            'firstName' => 'required|string|max:100',
            'lastName'  => 'required|string|max:100',
        ], $dynamic['rules']);

        $messages = array_merge([
            'firstName.required' => 'وارد کردن نام الزامی است.',
            'lastName.required'  => 'وارد کردن نام خانوادگی الزامی است.',
        ], $dynamic['messages']);

        $this->validate($rules, $messages);

        $fullName = trim("{$this->firstName} {$this->lastName}");
        $user->update(['name' => $fullName]);

        $technician->first_name = trim($this->firstName);
        $technician->last_name = trim($this->lastName);
        $technician->save();

        $saved = TechnicianCustomFieldsHelper::syncValues(
            $user,
            $technician,
            $profileFields,
            $this->customFields,
            $this->customFiles
        );

        // Sync technician skills/categories
        $selectedIds = array_values(array_unique(array_filter(array_map('intval', $this->selectedCategories))));
        if (!empty($selectedIds)) {
            $technician->skills()->whereNotIn('category_id', $selectedIds)->delete();
            foreach ($selectedIds as $catId) {
                if (Category::where('id', $catId)->exists()) {
                    TechnicianSkill::firstOrCreate([
                        'technician_id' => $technician->id,
                        'category_id'   => $catId,
                    ], [
                        'is_verified'      => ($technician->status === TechnicianStatus::ACTIVE),
                        'experience_years' => 1,
                    ]);
                }
            }
        } else {
            $technician->skills()->delete();
        }

        // Keep pending RegistrationRequest synced if one exists
        if (!empty($technician->mobile)) {
            $pendingReq = \App\Models\RegistrationRequest::where('mobile', $technician->mobile)
                ->where('status', 'pending')
                ->first();
            if ($pendingReq) {
                $existingReqFields = is_array($pendingReq->custom_fields) ? $pendingReq->custom_fields : [];
                $pendingReq->update([
                    'name'          => $fullName,
                    'custom_fields' => array_merge($existingReqFields, $saved, [
                        'categories' => $selectedIds,
                    ]),
                ]);
            }
        }

        foreach ($profileFields as $field) {
            if (strtolower($field->field_type ?? 'text') === 'file' && !empty($saved[$field->field_name])) {
                $this->existingFiles[$field->field_name] = (string) $saved[$field->field_name];
            }
        }

        $this->customFiles = [];
        $this->successMessage = 'اطلاعات پروفایل و احراز هویت با موفقیت ذخیره شد.';
    }

    public function render()
    {
        $profileFields = TechnicianCustomFieldsHelper::getFields('profile');
        $allData = ProvinceCity::getAll();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('fieldservice::livewire.technician.technician-profile-modal', [
            'profileFields' => $profileFields,
            'allData'       => $allData,
            'categories'    => $categories,
        ]);
    }
}
