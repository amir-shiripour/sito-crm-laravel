<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientSetting;
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

    public function mount(): void
    {
        $this->forms = ClientForm::orderBy('name')->get();

        // نقش‌ها (اسپاتی)
        $this->roles = Role::orderBy('name')->get(['id', 'name'])->toArray();

        // انتخاب فرم فعال اولیه
        $preferredKey = ClientSetting::getValue('default_form_key'); // اگر قبلاً ذخیره کرده بودی
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
        $id = $type.'_'.substr(str()->uuid()->toString(), 0, 8); // آیدی یکتا

        $this->schema['fields'][] = [
            'type'        => $type,
            'id'          => $id,
            'label'       => 'بی‌نام',
            'quick_create'=> false,
            'placeholder' => '',
            'width'       => 'full',   // full|1/2|1/3
            'group'       => '',       // نام گروه سفارشی
        ];

        $lastIndex = count($this->schema['fields']) - 1;

        // تنظیمات خاص بر اساس type
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
        $this->schema['fields'] = array_values($this->schema['fields']); // reindex
    }

    public function addSystemField(string $id): void
    {
        $defaults = ClientForm::systemFieldDefaults();

        if (! isset($defaults[$id])) {
            return;
        }

        // اگر قبلاً داخل فرم هست، دوباره اضافه نکن
        foreach ($this->schema['fields'] as $f) {
            if (($f['id'] ?? null) === $id) {
                return;
            }
        }

        $this->schema['fields'][] = $defaults[$id];
    }


    public function saveForm(): void
    {
        // اگر key خالی بود، خودکار بر اساس name ساخته شود
        if (blank($this->key) && !blank($this->name)) {
            $this->key = ClientForm::generateUniqueKey($this->name, $this->activeFormId);
        }

        $data = $this->validate([
            'name'       => 'required|string|max:100',
            'key'        => 'required|alpha_dash|max:100|unique:client_forms,key,'.($this->activeFormId ?? 'NULL').',id',
            'schema'     => 'required|array',
            'is_active'  => 'boolean',
        ]);

        // جلوگیری از استفاده از id فیلدهای سیستمی به‌عنوان meta
        $fields = $this->schema['fields'] ?? [];
        $normalized = [];

        foreach ($fields as $idx => $f) {
            $fid = trim($f['id'] ?? '');

            // اگر آیدی خالی بود، یک آیدی تصادفی تولید کن (برای فیلدهای سفارشی)
            if ($fid === '') {
                $fid = 'f_'.substr((string) Str::uuid(), 0, 8);
            }

            $f['id'] = $fid;

            $isReserved = array_key_exists($fid, ClientForm::SYSTEM_FIELDS);
            $isSystem   = !empty($f['is_system']);

            // اگر آیدی رزرو شده است ولی این فیلد سیستمی علامت نخورده، یعنی کاربر
            // می‌خواهد فیلد سفارشی با آیدی سیستمی بسازد ⇒ خطا
            if ($isReserved && !$isSystem) {
                throw ValidationException::withMessages([
                    'schema' => "آیدی «{$fid}» برای فیلد سیستمی رزرو شده و نمی‌تواند برای فیلد سفارشی استفاده شود.",
                ]);
            }

            // اگر واقعاً فیلد سیستمی است، نوع را طبق تعریف سیستم قفل کن و فلگ را ست کن
            if ($isReserved) {
                $sys = ClientForm::SYSTEM_FIELDS[$fid];
                $f['type']      = $sys['column'];     // نوع از سیستم
                $f['is_system'] = true;             // قفل به عنوان سیستمی
                // بقیه چیزها مثل label, placeholder, width, group, required, quick_create
                // همان تنظیمات فرم ساز می‌مانند
            } else {
                // فیلد سفارشی
                $f['is_system'] = false;
            }

            $normalized[] = $f;
        }

        $this->schema['fields'] = $normalized;

        $form = ClientForm::updateOrCreate(
            ['id' => $this->activeFormId],
            [
                'name'       => $this->name,
                'key'        => $this->key,
                'is_active' => $this->is_active, // اینجا به عنوان active ذخیره می‌کنیم
                'schema'     => $this->schema,
            ]
        );

        $this->activeFormId = $form->id;

        // رفرش لیست
        $this->forms = ClientForm::orderBy('name')->get();

        $this->dispatch('notify', type: 'success', text: 'فرم ذخیره شد.');
    }

    public function render()
    {
        return view('clients::user.settings.forms-builder', [
            'forms' => $this->forms,
            'roles' => $this->roles,
        ]);
    }
}
