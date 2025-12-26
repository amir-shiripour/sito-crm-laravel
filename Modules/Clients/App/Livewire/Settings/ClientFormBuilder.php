<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientSetting;
use Modules\Clients\Entities\ClientStatus;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

#[Layout('layouts.user')]
class ClientFormBuilder extends Component
{
    /** @var \Illuminate\Support\Collection */
    public $forms;

    public $activeFormId = null;

    public string $name = '';
    public string $key  = '';
    public bool $is_active = true;

    public array $schema = ['fields' => []];
    public array $systemFieldIds = ['full_name', 'phone', 'email', 'national_code', 'notes', 'status_id', 'password'];

    // لیست نقش‌ها برای select-user-by-role
    public array $roles = [];

    // لیست وضعیت‌ها برای "الزامی بر اساس وضعیت"
    public array $statuses = [];

    // حالت مرتب‌سازی فیلدها
    public bool $reorderMode = false;
    public ?array $schemaBackup = null;

    public function mount(): void
    {
        $this->forms = ClientForm::orderBy('name')->get();

        // نقش‌ها (اسپاتی)
        $this->roles = Role::orderBy('name')->get(['id', 'name'])->toArray();

        // وضعیت‌ها برای required_status_keys
        $this->statuses = ClientStatus::active()
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label'])
            ->toArray();

        // انتخاب فرم فعال اولیه
        $preferredKey = ClientSetting::getValue('default_form_key');
        if ($preferredKey && $form = ClientForm::where('key', $preferredKey)->first()) {
            $this->loadForm($form->id);
        } elseif ($default = ClientForm::active()) {
            $this->loadForm($default->id);
        }
    }

    // فرم جدید (ریست state)
    public function newForm(): void
    {
        $this->activeFormId = null;
        $this->name = '';
        $this->key  = '';
        $this->is_active = true;
        $this->schema = ['fields' => []];
    }

    public function loadForm($id): void
    {
        $form = ClientForm::findOrFail($id);

        $this->activeFormId = $form->id;
        $this->name         = $form->name;
        $this->key          = $form->key;
        $this->is_active    = (bool) $form->is_active;
        $this->schema       = $form->schema ?? ['fields' => []];

        $this->normalizeSchemaState();
    }

    // نرمال‌سازی state سمت Livewire (فقط برای UI)
    private function normalizeSchemaState(): void
    {
        if (!isset($this->schema['fields']) || !is_array($this->schema['fields'])) {
            $this->schema['fields'] = [];
            return;
        }

        foreach ($this->schema['fields'] as &$f) {
            // مطمئن شو required_status_keys همیشه آرایه است
            if (!isset($f['required_status_keys']) || !is_array($f['required_status_keys'])) {
                $f['required_status_keys'] = [];
            }
            // مطمئن شو conditional_required همیشه آرایه است
            if (!isset($f['conditional_required']) || !is_array($f['conditional_required'])) {
                $f['conditional_required'] = [];
            }
        }
        unset($f);
    }

    // حذف فرم
    public function deleteForm($id): void
    {
        $form = ClientForm::findOrFail($id);
        $form->delete();

        $this->forms = ClientForm::orderBy('name')->get();

        if ($this->activeFormId == $id) {
            $this->newForm();
        }

        $this->dispatch('notify', type: 'success', text: 'فرم حذف شد.');
    }

    // افزودن فیلد
    public function addField(string $type): void
    {
        $id = $type . '_' . substr(str()->uuid()->toString(), 0, 8);

        $this->schema['fields'][] = [
            'type'                => $type,
            'id'                  => $id,
            'label'               => 'بی‌نام',
            'quick_create'        => false,
            'placeholder'         => '',
            'width'               => 'full',
            'group'               => '',
            'required_status_keys' => [], // مهم: از اول آرایه باشد
            'conditional_required' => [], // قوانین شرطی برای الزامی شدن
        ];

        $lastIndex = count($this->schema['fields']) - 1;

        if ($type === 'select') {
            $this->schema['fields'][$lastIndex]['options_json'] = '';
            $this->schema['fields'][$lastIndex]['multiple'] = false;
            $this->schema['fields'][$lastIndex]['use_clients_list'] = false;
        }

        if ($type === 'select-user-by-role') {
            $this->schema['fields'][$lastIndex]['role']                = null;
            $this->schema['fields'][$lastIndex]['multiple']            = false;
            $this->schema['fields'][$lastIndex]['lock_current_if_role'] = false;
        }

        if ($type === 'file') {
            $this->schema['fields'][$lastIndex]['max_mb'] = null;
            $this->schema['fields'][$lastIndex]['accept'] = '';
        }
    }

    // حذف یک فیلد
    public function removeField(int $index): void
    {
        if (! isset($this->schema['fields'][$index])) {
            return;
        }
        unset($this->schema['fields'][$index]);
        $this->schema['fields'] = array_values($this->schema['fields']);
    }

    public function addSystemField(string $id): void
    {
        $defaults = ClientForm::systemFieldDefaults();

        if (! isset($defaults[$id])) {
            return;
        }

        foreach ($this->schema['fields'] as $f) {
            if (($f['id'] ?? null) === $id) {
                return;
            }
        }

        $field = $defaults[$id];

        // اگر در تعریف پیش‌فرض چیزی نیامده، مطمئن شو آرایه است
        if (!isset($field['required_status_keys']) || !is_array($field['required_status_keys'])) {
            $field['required_status_keys'] = [];
        }
        if (!isset($field['conditional_required']) || !is_array($field['conditional_required'])) {
            $field['conditional_required'] = [];
        }

        $this->schema['fields'][] = $field;
    }


    public function saveForm(): void
    {
        if (blank($this->key) && !blank($this->name)) {
            $this->key = ClientForm::generateUniqueKey($this->name, $this->activeFormId);
        }

        $this->validate([
            'name'       => 'required|string|max:100',
            'key'        => 'required|alpha_dash|max:100|unique:client_forms,key,' . ($this->activeFormId ?? 'NULL') . ',id',
            'schema'     => 'required|array',
            'is_active'  => 'boolean',
        ]);

        $fields     = $this->schema['fields'] ?? [];
        $normalized = [];

        foreach ($fields as $idx => $f) {
            $fid = trim($f['id'] ?? '');

            if ($fid === '') {
                $fid = 'f_' . substr((string) Str::uuid(), 0, 8);
            }

            $f['id'] = $fid;

            $isReserved = array_key_exists($fid, ClientForm::SYSTEM_FIELDS);
            $isSystem   = !empty($f['is_system']);

            if ($isReserved && !$isSystem) {
                throw ValidationException::withMessages([
                    'schema' => "آیدی «{$fid}» برای فیلد سیستمی رزرو شده و نمی‌تواند برای فیلد سفارشی استفاده شود.",
                ]);
            }

            if ($isReserved) {
                $sys = ClientForm::systemFieldDefaults()[$fid] ?? null;
                if ($sys) {
                    // نوع واقعی از systemFieldDefaults
                    $f['type']      = $sys['type'];
                    $f['is_system'] = true;
                }
            } else {
                $f['is_system'] = false;
            }

            // نرمال‌سازی required_status_keys → همیشه آرایه از keyهای غیرخالی
            $keys = $f['required_status_keys'] ?? [];
            if (!is_array($keys)) {
                $keys = $keys ? [$keys] : [];
            }
            $keys = array_values(array_filter($keys, fn($k) => is_string($k) && $k !== ''));
            $f['required_status_keys'] = $keys;

            // نرمال‌سازی conditional_required → همیشه آرایه از قوانین معتبر
            $conditionalRules = $f['conditional_required'] ?? [];
            if (!is_array($conditionalRules)) {
                $conditionalRules = [];
            }
            // فیلتر کردن قوانین معتبر (باید trigger_field_id داشته باشند)
            $conditionalRules = array_values(array_filter($conditionalRules, function ($rule) {
                return is_array($rule) && !empty($rule['trigger_field_id']);
            }));
            $f['conditional_required'] = $conditionalRules;

            $normalized[] = $f;
        }

        $this->schema['fields'] = $normalized;

        $form = ClientForm::updateOrCreate(
            ['id' => $this->activeFormId],
            [
                'name'       => $this->name,
                'key'        => $this->key,
                'is_active'  => $this->is_active,
                'schema'     => $this->schema,
            ]
        );

        $this->activeFormId = $form->id;
        $this->forms = ClientForm::orderBy('name')->get();

        $this->dispatch('notify', type: 'success', text: 'فرم ذخیره شد.');
    }

    // افزودن قانون شرطی به یک فیلد
    public function addConditionalRule(int $fieldIndex): void
    {
        if (!isset($this->schema['fields'][$fieldIndex])) {
            return;
        }

        if (!isset($this->schema['fields'][$fieldIndex]['conditional_required'])) {
            $this->schema['fields'][$fieldIndex]['conditional_required'] = [];
        }

        $this->schema['fields'][$fieldIndex]['conditional_required'][] = [
            'trigger_field_id' => '',
            'operator' => 'filled', // filled, empty, equals, not_equals
            'value' => '', // برای equals/not_equals
        ];
    }

    // حذف قانون شرطی از یک فیلد
    public function removeConditionalRule(int $fieldIndex, int $ruleIndex): void
    {
        if (!isset($this->schema['fields'][$fieldIndex]['conditional_required'][$ruleIndex])) {
            return;
        }

        unset($this->schema['fields'][$fieldIndex]['conditional_required'][$ruleIndex]);
        $this->schema['fields'][$fieldIndex]['conditional_required'] = array_values(
            $this->schema['fields'][$fieldIndex]['conditional_required']
        );
    }

    // فعال/غیرفعال کردن حالت مرتب‌سازی
    public function toggleReorderMode(): void
    {
        if (!$this->reorderMode) {
            // ذخیره backup قبل از ورود به حالت مرتب‌سازی
            $this->schemaBackup = $this->schema;
            $this->reorderMode = true;
            $this->dispatch('reorderModeChanged');
        } else {
            // لغو: بازگشت به حالت قبل
            if ($this->schemaBackup !== null) {
                $this->schema = $this->schemaBackup;
            }
            $this->reorderMode = false;
            $this->schemaBackup = null;
            $this->dispatch('reorderModeChanged');
        }
    }

    // تایید مرتب‌سازی
    public function confirmReorder(): void
    {
        $this->reorderMode = false;
        $this->schemaBackup = null;
        $this->dispatch('reorderModeChanged');
        $this->dispatch('notify', type: 'success', text: 'ترتیب فیلدها ذخیره شد.');
    }

    // مرتب‌سازی ترتیب گروه‌ها
    public function reorderGroups(array $groupNames): void
    {
        if (!$this->reorderMode) {
            return;
        }

        // ایجاد map از فیلدها بر اساس گروه
        $fieldsByGroup = [];
        foreach ($this->schema['fields'] as $field) {
            $fieldGroup = $field['group'] ?? '';
            $normalizedGroup = $fieldGroup === '' ? '__no_group__' : $fieldGroup;

            if (!isset($fieldsByGroup[$normalizedGroup])) {
                $fieldsByGroup[$normalizedGroup] = [];
            }
            $fieldsByGroup[$normalizedGroup][] = $field;
        }

        // مرتب‌سازی فیلدها بر اساس ترتیب جدید گروه‌ها
        $result = [];
        foreach ($groupNames as $groupName) {
            $normalizedGroup = $groupName === '' ? '__no_group__' : $groupName;
            if (isset($fieldsByGroup[$normalizedGroup])) {
                $result = array_merge($result, $fieldsByGroup[$normalizedGroup]);
            }
        }

        // اضافه کردن گروه‌هایی که در لیست جدید نیستند
        foreach ($fieldsByGroup as $group => $fields) {
            if (!in_array($group === '__no_group__' ? '' : $group, $groupNames)) {
                $result = array_merge($result, $fields);
            }
        }

        $this->schema['fields'] = $result;
    }

    // مرتب‌سازی فیلدها در یک گروه
    public function reorderFields(string $group, array $fieldIds): void
    {
        if (!$this->reorderMode) {
            return;
        }

        // نرمال‌سازی نام گروه (برای مقایسه)
        $normalizedGroup = $group === '' ? '__no_group__' : $group;

        // 1. حفظ ترتیب فعلی گروه‌ها از schema
        $groupOrder = [];
        $seenGroups = [];
        foreach ($this->schema['fields'] as $field) {
            $fieldGroup = $field['group'] ?? '';
            $normalizedFieldGroup = $fieldGroup === '' ? '__no_group__' : $fieldGroup;

            if (!in_array($normalizedFieldGroup, $seenGroups)) {
                $groupOrder[] = $normalizedFieldGroup;
                $seenGroups[] = $normalizedFieldGroup;
            }
        }

        // 2. ایجاد map از فیلدها بر اساس گروه
        $fieldsByGroup = [];
        foreach ($this->schema['fields'] as $field) {
            $fieldId = $field['id'] ?? '';
            $fieldGroup = $field['group'] ?? '';
            $normalizedFieldGroup = $fieldGroup === '' ? '__no_group__' : $fieldGroup;

            if (!isset($fieldsByGroup[$normalizedFieldGroup])) {
                $fieldsByGroup[$normalizedFieldGroup] = [];
            }
            $fieldsByGroup[$normalizedFieldGroup][$fieldId] = $field;
        }

        // 3. مرتب‌سازی فیلدهای گروه مورد نظر بر اساس ترتیب جدید
        $reorderedGroupFields = [];
        foreach ($fieldIds as $fieldId) {
            if (isset($fieldsByGroup[$normalizedGroup][$fieldId])) {
                $reorderedGroupFields[] = $fieldsByGroup[$normalizedGroup][$fieldId];
            }
        }

        // اضافه کردن فیلدهایی که در لیست جدید نیستند (در صورت وجود)
        foreach ($fieldsByGroup[$normalizedGroup] ?? [] as $fieldId => $field) {
            if (!in_array($fieldId, $fieldIds)) {
                $reorderedGroupFields[] = $field;
            }
        }

        // 4. به‌روزرسانی فیلدهای گروه مرتب‌سازی شده
        $fieldsByGroup[$normalizedGroup] = [];
        foreach ($reorderedGroupFields as $field) {
            $fieldId = $field['id'] ?? '';
            $fieldsByGroup[$normalizedGroup][$fieldId] = $field;
        }

        // 5. ترکیب مجدد فیلدها بر اساس ترتیب گروه‌ها
        $result = [];
        foreach ($groupOrder as $groupName) {
            if (isset($fieldsByGroup[$groupName])) {
                foreach ($fieldsByGroup[$groupName] as $field) {
                    $result[] = $field;
                }
            }
        }

        $this->schema['fields'] = $result;
    }

    public function render()
    {
        return view('clients::user.settings.forms-builder', [
            'forms'    => $this->forms,
            'roles'    => $this->roles,
            'statuses' => $this->statuses,
        ]);
    }
}
