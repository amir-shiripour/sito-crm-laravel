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
    public array $systemFieldIds = ['full_name','phone','email','national_code','notes','status_id'];

    // لیست نقش‌ها برای select-user-by-role
    public array $roles = [];

    // لیست وضعیت‌ها برای "الزامی بر اساس وضعیت"
    public array $statuses = [];

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
        $id = $type.'_'.substr(str()->uuid()->toString(), 0, 8);

        $this->schema['fields'][] = [
            'type'                => $type,
            'id'                  => $id,
            'label'               => 'بی‌نام',
            'quick_create'        => false,
            'placeholder'         => '',
            'width'               => 'full',
            'group'               => '',
            'required_status_keys'=> [], // مهم: از اول آرایه باشد
        ];

        $lastIndex = count($this->schema['fields']) - 1;

        if ($type === 'select') {
            $this->schema['fields'][$lastIndex]['options_json'] = '';
        }

        if ($type === 'select-user-by-role') {
            $this->schema['fields'][$lastIndex]['role']                = null;
            $this->schema['fields'][$lastIndex]['multiple']            = false;
            $this->schema['fields'][$lastIndex]['lock_current_if_role']= false;
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

        $this->schema['fields'][] = $field;
    }


    public function saveForm(): void
    {
        if (blank($this->key) && !blank($this->name)) {
            $this->key = ClientForm::generateUniqueKey($this->name, $this->activeFormId);
        }

        $this->validate([
            'name'       => 'required|string|max:100',
            'key'        => 'required|alpha_dash|max:100|unique:client_forms,key,'.($this->activeFormId ?? 'NULL').',id',
            'schema'     => 'required|array',
            'is_active'  => 'boolean',
        ]);

        $fields     = $this->schema['fields'] ?? [];
        $normalized = [];

        foreach ($fields as $idx => $f) {
            $fid = trim($f['id'] ?? '');

            if ($fid === '') {
                $fid = 'f_'.substr((string) Str::uuid(), 0, 8);
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
                $f['is_system'] = true;
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

    public function render()
    {
        return view('clients::user.settings.forms-builder', [
            'forms'    => $this->forms,
            'roles'    => $this->roles,
            'statuses' => $this->statuses,
        ]);
    }
}
