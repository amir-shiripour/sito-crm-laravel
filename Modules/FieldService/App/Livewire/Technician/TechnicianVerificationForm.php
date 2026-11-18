<?php

namespace Modules\FieldService\App\Livewire\Technician;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\Section;
use Modules\FieldService\App\Models\ServiceGroup;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Models\TechnicianSkill;
use Modules\FieldService\App\Support\TechnicianCustomFieldsHelper;
use Spatie\Permission\Models\Role;

class TechnicianVerificationForm extends Component
{
    use WithFileUploads;

    public bool $isInitial = false;
    public array $customFields = [];
    public array $customFiles = [];
    public array $existingFiles = [];
    public array $selectedCategories = [];
    public string $activeSectionTab = '';
    public string $specialtySearch = '';
    public ?string $errorMessage = null;
    public ?string $successMessage = null;
    public ?string $infoMessage = null;

    public function mount(bool $isInitial = false): void
    {
        $this->isInitial = $isInitial;
        $this->loadData();
    }

    protected function loadData(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $technician = Technician::where('user_id', $user->id)->first();
        if (!$technician) {
            return;
        }

        $this->selectedCategories = $technician->categories()
            ->pluck('field_service_categories.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if ($this->isInitial) {
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
        }
    }

    public function selectSectionTab(string $tab): void
    {
        $this->activeSectionTab = $tab;
        $this->specialtySearch = '';
    }

    public function clearSpecialtySearch(): void
    {
        $this->specialtySearch = '';
    }

    public function checkApprovalStatus(): void
    {
        $this->infoMessage = null;
        $user = Auth::user();
        $technician = $user ? Technician::where('user_id', $user->id)->first() : null;

        if (!$user || !$technician) {
            return;
        }

        if ($this->isInitial && $technician->status === TechnicianStatus::ACTIVE) {
            session()->flash('success', 'احراز هویت شما با موفقیت تایید شد و حساب تکنسین فعال گردید.');
            $this->redirect(route('profile.show'), navigate: false);
            return;
        }

        $mobile = $technician->mobile ?: $user->mobile;
        $latestRequest = $mobile
            ? \App\Models\RegistrationRequest::where('mobile', $mobile)->latest('id')->first()
            : null;

        if ($latestRequest && $latestRequest->status === 'rejected') {
            $this->loadData();
            return;
        }

        $this->infoMessage = 'درخواست شما همچنان در حال بررسی توسط مدیریت است.';
    }

    public function removeCategory(int $catId): void
    {
        $this->selectedCategories = array_values(array_filter(
            array_map('intval', $this->selectedCategories),
            fn (int $id) => $id !== $catId
        ));
    }

    public function clearCategories(): void
    {
        $this->selectedCategories = [];
    }

    public function toggleGroupCategories(int $groupId): void
    {
        $groupServiceIds = Category::where('is_active', true)
            ->where('group_id', $groupId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $this->toggleCategoryBatch($groupServiceIds);
    }

    public function toggleSectionDirectCategories(int $sectionId): void
    {
        $directServiceIds = Category::where('is_active', true)
            ->where('section_id', $sectionId)
            ->whereNull('group_id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $this->toggleCategoryBatch($directServiceIds);
    }

    protected function toggleCategoryBatch(array $batchIds): void
    {
        if (empty($batchIds)) {
            return;
        }

        $current = array_map('intval', $this->selectedCategories);
        $allSelected = empty(array_diff($batchIds, $current));

        if ($allSelected) {
            $this->selectedCategories = array_values(array_diff($current, $batchIds));
        } else {
            $this->selectedCategories = array_values(array_unique(array_merge($current, $batchIds)));
        }
    }

    public function saveVerification(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $user = Auth::user();
        $technician = $user ? Technician::where('user_id', $user->id)->first() : null;

        if (!$user || !$technician) {
            $this->errorMessage = 'پرونده تکنسین یافت نشد.';
            return;
        }

        $profileFields = TechnicianCustomFieldsHelper::getFields('profile');

        if ($this->isInitial) {
            $dynamic = TechnicianCustomFieldsHelper::buildValidation($profileFields, $this->existingFiles, false);

            $rules = array_merge([
                'selectedCategories' => 'required|array|min:1',
            ], $dynamic['rules']);

            $messages = array_merge([
                'selectedCategories.required' => 'انتخاب حداقل یک تخصص یا حوزه کاری الزامی است.',
                'selectedCategories.min'      => 'انتخاب حداقل یک تخصص یا حوزه کاری الزامی است.',
            ], $dynamic['messages']);

            $this->validate($rules, $messages);

            $nameParts = explode(' ', trim((string) $user->name), 2);
            $technician->first_name = $nameParts[0] ?: $technician->first_name;
            $technician->last_name = $nameParts[1] ?? $technician->last_name;

            $saved = TechnicianCustomFieldsHelper::syncValues(
                $user,
                $technician,
                $profileFields,
                $this->customFields,
                $this->customFiles
            );

            foreach ($profileFields as $field) {
                if (strtolower($field->field_type ?? 'text') === 'file' && !empty($saved[$field->field_name])) {
                    $this->existingFiles[$field->field_name] = (string) $saved[$field->field_name];
                }
            }
            $this->customFiles = [];
        } else {
            $this->validate([
                'selectedCategories' => 'required|array|min:1',
            ], [
                'selectedCategories.required' => 'انتخاب حداقل یک تخصص یا حوزه کاری الزامی است.',
                'selectedCategories.min'      => 'انتخاب حداقل یک تخصص یا حوزه کاری الزامی است.',
            ]);

            $existingValues = TechnicianCustomFieldsHelper::loadExistingValues($user, $technician, $profileFields);
            $saved = TechnicianCustomFieldsHelper::syncValues($user, $technician, $profileFields, $existingValues, []);
        }

        // Sync categories / skills
        $selectedIds = array_values(array_unique(array_filter(array_map('intval', $this->selectedCategories))));
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

        // Transition verification status based on /settings#registration approval type
        $techRole = Role::where('name', 'technician')->first();
        $regSetting = \Modules\Settings\Entities\Setting::where('key', 'registration')->first();
        $regConfig = $regSetting && $regSetting->value ? json_decode($regSetting->value, true) : [];
        $approvalType = ($techRole && isset($regConfig[$techRole->id]['approval']))
            ? $regConfig[$techRole->id]['approval']
            : 'manual';
        $isManualApproval = ($approvalType === 'manual');

        $mobile = $technician->mobile ?: $user->mobile;
        $isRejectedRequest = $mobile
            ? \App\Models\RegistrationRequest::where('mobile', $mobile)->where('status', 'rejected')->exists()
            : false;

        $meta = is_array($technician->meta) ? $technician->meta : [];
        $meta['verification_submitted'] = true;
        $technician->meta = $meta;

        if ($technician->status !== TechnicianStatus::ACTIVE && ($technician->status !== TechnicianStatus::SUSPENDED || $isRejectedRequest)) {
            $technician->status = $isManualApproval
                ? TechnicianStatus::PENDING_APPROVAL
                : TechnicianStatus::ACTIVE;
            if (!$isManualApproval) {
                $technician->verified_at = now();
            }
        }
        $technician->save();

        if ($technician->status === TechnicianStatus::ACTIVE) {
            $technician->skills()->update(['is_verified' => true]);
            \Modules\FieldService\App\Services\TechnicianLifecycleService::triggerApprovalContract($technician);
        }

        if ($isManualApproval && $technician->status === TechnicianStatus::PENDING_APPROVAL) {
            \App\Models\RegistrationRequest::updateOrCreate(
                ['mobile' => $mobile],
                [
                    'role_id'          => $techRole?->id,
                    'name'             => $technician->full_name ?: $user->name,
                    'email'            => $user->email,
                    'mobile'           => $mobile,
                    'password'         => $user->password,
                    'status'           => 'pending',
                    'rejection_reason' => null,
                    'reviewed_by'      => null,
                    'reviewed_at'      => null,
                    'custom_fields'    => array_merge($saved, [
                        'categories' => $selectedIds,
                    ]),
                ]
            );
            $this->successMessage = 'اطلاعات احراز هویت و تخصص‌های شما ثبت شد و پرونده شما برای تایید نهایی به مدیریت ارسال گردید.';
        } else {
            if ($this->isInitial && $technician->status === TechnicianStatus::ACTIVE) {
                session()->flash('success', 'احراز هویت شما با موفقیت انجام و تایید شد. از این پس می‌توانید اطلاعات و تخصص‌های خود را در تب «اطلاعات کاربری» مدیریت کنید.');
                $this->redirect(route('profile.show'), navigate: false);
                return;
            }
            $this->successMessage = 'تخصص‌ها و حوزه‌های کاری شما با موفقیت ذخیره شد.';
        }
    }

    public function render()
    {
        $user = Auth::user();
        $technician = $user ? Technician::where('user_id', $user->id)->first() : null;
        $profileFields = $this->isInitial ? TechnicianCustomFieldsHelper::getFields('profile') : collect();

        $sections = Section::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with([
                'groups' => fn ($q) => $q->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->with([
                        'services' => fn ($sq) => $sq->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('id'),
                    ]),
                'services' => fn ($q) => $q->where('is_active', true)
                    ->whereNull('group_id')
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->get();

        $ungroupedCategories = Category::where('is_active', true)
            ->whereNull('section_id')
            ->whereNull('group_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $categories = Category::with(['section', 'group.section'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($this->activeSectionTab === '') {
            if ($sections->isNotEmpty()) {
                $this->activeSectionTab = (string) $sections->first()->id;
            } elseif ($ungroupedCategories->isNotEmpty()) {
                $this->activeSectionTab = 'ungrouped';
            }
        }

        $needsVerification = $user ? TechnicianCustomFieldsHelper::needsVerification($user, $technician) : false;
        $mobile = $technician?->mobile ?: $user?->mobile;
        $latestRegRequest = $mobile
            ? \App\Models\RegistrationRequest::where('mobile', $mobile)->latest('id')->first()
            : null;

        return view('fieldservice::livewire.technician.technician-verification-form', [
            'technician'          => $technician,
            'profileFields'       => $profileFields,
            'sections'            => $sections,
            'ungroupedCategories' => $ungroupedCategories,
            'categories'          => $categories,
            'needsVerification'   => $needsVerification,
            'latestRegRequest'    => $latestRegRequest,
        ]);
    }
}
