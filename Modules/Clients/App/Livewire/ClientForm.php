<?php

namespace Modules\Clients\App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Clients\Entities\ClientStatus;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientForm as ClientFormSchema;
use Modules\Clients\Entities\ClientSetting;
use App\Models\User;
use Illuminate\Support\Str;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;

#[Layout('layouts.user')]
class ClientForm extends Component
{
    use WithFileUploads;
    use FileUploadTrait;

    // بایندهای استاندارد فرم
    public ?Client $client = null;

    public ?string $username = null;
    public string $full_name = '';

    public ?string $email = null;
    public ?string $phone = null;
    public ?string $national_code = null;
    public ?string $case_number = null;
    public ?string $notes = null;

    // 🔹 فیلدهای مربوط به ورود کلاینت
    public ?string $password = null;
    public ?string $password_confirmation = null; // برای auto-generate فقط
    public bool $auto_generate_password = false;

    public array $meta = [];
    public array $upload_files = [];
    public $status_id = null;

    // اسکیمای فرم پویا (از ClientFormSchema)
    public array $schema = ['fields' => []];

    // استیت ایجاد سریع
    public array $quick = [];
    public array $availableStatuses = [];

    public bool $asQuickWidget = false;
    public bool $isQuickMode   = false;

    // 🔸 حالت اختصاصی ویجت داشبورد
    public bool $forWidget = false;

    // 🔸 ریفرنس به فرم فعال (برای استفاده از quickFields و ... در ویو)
    public ?ClientFormSchema $formDefinition = null;

    /**
     * دکمه "ایجاد خودکار پسورد" در UI
     * - روی فرم کامل و کوئیک استفاده می‌شود
     */
    public function generatePassword(): void
    {
        $plain = Str::random(12);
        $this->password = $plain;
        $this->password_confirmation = $plain;
        $this->auto_generate_password = true;
    }

    public function mount(?Client $client = null, ?string $formKey = null, bool $forWidget = false)
    {
        $this->forWidget = $forWidget;

        // اگر برای ایجاد جدید فراخوانی شده، client تهی است
        $this->client = $client ?? new Client();
        $isEdit       = $client && $client->exists;

        // انتخاب فرم فعال: تنظیمات → default → آخرین
        $keyFromSettings = ClientSetting::getValue('default_form_key');
        $form = $formKey
            ? ClientFormSchema::where('key', $formKey)->first()
            : ClientFormSchema::active($keyFromSettings);

        $this->formDefinition = $form;
        $this->schema         = ($form && isset($form->schema)) ? $form->schema : ['fields' => []];

        // وضعیت‌های فعال
        $statuses = ClientStatus::active()->get();

        $currentStatusId  = $isEdit ? $client->status_id : null;
        $currentStatusKey = $isEdit ? optional($client->status)->key : null;

        // اعمال وابستگی allowed_from
        $this->availableStatuses = $statuses->filter(
            function (ClientStatus $st) use ($currentStatusId, $currentStatusKey) {
                $allowed = $st->allowed_from ?? null;

                if (empty($allowed)) {
                    return true; // از هر وضعیتی می‌شود به این رسید
                }

                if (!$currentStatusId) {
                    return false; // هنوز وضعیت فعلی نداریم ولی این وضعیت وابسته است
                }

                return in_array($currentStatusKey, $allowed, true);
            }
        )->values()->all();

        if ($isEdit) {
            $this->username      = $client->username;
            $this->full_name     = (string) $client->full_name;
            $this->email         = $client->email;
            $this->phone         = $client->phone;
            $this->national_code = $client->national_code;
            $this->case_number   = $client->case_number;
            $this->notes         = $client->notes;
            $this->meta          = $this->convertMetaDatesForDisplay($client->meta ?? [], $form);
            $this->status_id     = $client->status_id;

            // بارگذاری کاربران متصل شده برای فیلدهای select-user-by-role
            foreach ($this->schema['fields'] as $f) {
                if (($f['type'] ?? null) === 'select-user-by-role' && !empty($f['role'])) {
                    $fid = $f['id'] ?? null;
                    if ($fid) {
                        $roleUserIds = $client->users()
                            ->whereHas('roles', function($q) use ($f) {
                                $q->where('name', $f['role']);
                            })
                            ->pluck('users.id')
                            ->toArray();

                        if (!empty($f['multiple'])) {
                            $this->meta[$fid] = array_map('strval', $roleUserIds);
                        } else {
                            $this->meta[$fid] = count($roleUserIds) > 0 ? (string)$roleUserIds[0] : null;
                        }
                    }
                }
            }

            // برای ویرایش، پسورد را خالی می‌گذاریم (اگر پر شود یعنی تغییر پسورد)
            $this->password = null;
            $this->password_confirmation = null;
            $this->auto_generate_password = false;
        } else {
            $this->username      = null;
            $this->full_name     = '';
            $this->email         = null;
            $this->phone         = null;
            $this->national_code = null;
            $this->case_number   = null;
            $this->notes         = null;
            $this->meta          = [];

            // مقداردهی اولیه برای فیلدهای select-user-by-role (مثل قفل شدن روی کاربر فعلی)
            foreach ($this->schema['fields'] as $f) {
                if (($f['type'] ?? null) === 'select-user-by-role' && !empty($f['role'])) {
                    $fid = $f['id'] ?? null;
                    if ($fid) {
                        if (!empty($f['lock_current_if_role']) && Auth::user()?->hasRole($f['role'])) {
                            if (!empty($f['multiple'])) {
                                $this->meta[$fid] = [(string)Auth::id()];
                            } else {
                                $this->meta[$fid] = (string)Auth::id();
                            }
                        } else {
                            $this->meta[$fid] = !empty($f['multiple']) ? [] : null;
                        }
                    }
                }
            }

            // تنظیم وضعیت پیش‌فرض روی اولین وضعیت فعال
            $firstStatus = $statuses->first();
            $this->status_id = $firstStatus ? $firstStatus->id : null;

            // تنظیم وضعیت پیش‌فرض برای فرم quick
            if ($this->status_id) {
                $this->quick['status_id'] = $this->status_id;
            }

            $this->password      = null;
            $this->password_confirmation = null;
            $this->auto_generate_password = false;
        }
    }

    public function render()
    {
        // 🔸 سه حالت:
        // ۱) فرم کامل (dynamic-form)
        // ۲) quick-widget قدیمی (مودال در صفحه‌ی کلاینت‌ها)
        // ۳) quick-widget اختصاصی ویجت داشبورد (فرم inline داخل کارت ویجت)
        if ($this->asQuickWidget) {
            // حالت اختصاصی ویجت → ویوی مخصوص ویجت
            if ($this->forWidget) {
                $quickFields = $this->formDefinition?->quickFields() ?? [];

                return view('clients::widgets.client-quick-form', [
                    'quickFields' => $quickFields,
                ]);
            }

            // حالت قبلی (مثلاً مودال در لیست مشتریان)
            return view('clients::user.clients.quick-widget');
        }

        // فرم کامل
        return view('clients::user.clients.dynamic-form');
    }

    // Helper برای select-user-by-role
    public function usersForRole(?string $role)
    {
        if (!$role) return collect();
        return User::role($role)->select('id', 'name')->orderBy('name')->get();
    }

    /**
     * پیدا کردن key وضعیت از روی id
     */
    private function resolveStatusKey($statusId): ?string
    {
        if (!$statusId) {
            return null;
        }

        // ابتدا در availableStatuses بگرد
        $candidate = collect($this->availableStatuses)->first(function ($st) use ($statusId) {
            if (is_array($st)) {
                return (int) ($st['id'] ?? 0) === (int) $statusId;
            }
            return (int) $st->id === (int) $statusId;
        });

        if ($candidate) {
            return is_array($candidate) ? ($candidate['key'] ?? null) : $candidate->key;
        }

        // اگر نبود، مستقیم از دیتابیس بخوان
        $obj = ClientStatus::find($statusId);
        return $obj?->key;
    }

    /**
     * بررسی اینکه آیا یک قانون شرطی فعال است یا نه
     */
    private function isConditionalRuleActive(array $rule, array $allFields, bool $forQuick = false): bool
    {
        $triggerFieldId = $rule['trigger_field_id'] ?? null;
        if (!$triggerFieldId) {
            return false;
        }

        // تعیین مسیر داده (سیستمی یا meta)
        $systemModelMap = [
            'full_name' => 'full_name',
            'phone' => 'phone',
            'email' => 'email',
            'national_code' => 'national_code',
            'case_number' => 'case_number',
            'notes' => 'notes',
            'password' => 'password',
        ];

        $isSystem = array_key_exists($triggerFieldId, ClientFormSchema::SYSTEM_FIELDS);

        if ($forQuick) {
            $triggerValue = $this->quick[$triggerFieldId] ?? null;
        } else {
            if ($isSystem) {
                $prop = $systemModelMap[$triggerFieldId] ?? $triggerFieldId;
                $triggerValue = $this->{$prop} ?? null;
            } else {
                $triggerValue = $this->meta[$triggerFieldId] ?? null;
            }
        }

        $operator = $rule['operator'] ?? 'filled';
        $expectedValue = $rule['value'] ?? '';

        // تبدیل مقدار به string برای مقایسه
        if (is_array($triggerValue)) {
            $triggerValue = json_encode($triggerValue);
        } else {
            $triggerValue = (string) $triggerValue;
        }

        switch ($operator) {
            case 'filled':
                return !empty($triggerValue) && trim($triggerValue) !== '';
            case 'empty':
                return empty($triggerValue) || trim($triggerValue) === '';
            case 'equals':
                return trim($triggerValue) === trim($expectedValue);
            case 'not_equals':
                return trim($triggerValue) !== trim($expectedValue);
            default:
                return false;
        }
    }

    /**
     * بررسی اینکه آیا یک فیلد باید به دلیل قوانین شرطی الزامی باشد
     */
    private function isFieldRequiredByConditional(array $field, array $allFields, bool $forQuick = false): bool
    {
        $conditionalRules = $field['conditional_required'] ?? [];
        if (empty($conditionalRules) || !is_array($conditionalRules)) {
            return false;
        }

        foreach ($conditionalRules as $rule) {
            if ($this->isConditionalRuleActive($rule, $allFields, $forQuick)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ساخت قوانین ولیدیشن برای فیلدهای سیستمی
     */
    private function buildSystemValidationRules(bool $forQuick = false, ?string $targetStatusKey = null): array
    {
        $rules         = [];
        $schemaFields  = collect($this->schema['fields'] ?? []);
        $defaultFields = ClientFormSchema::systemFieldDefaults();

        // رول‌های پایه برای هر فیلد سیستمی
        $baseRules = [
            'full_name'     => ['string', 'max:255'],
            'phone'         => ['string'],
            'email'         => ['email'],
            'national_code' => ['string', 'max:20'],
            'case_number'   => ['string', 'max:100'],
            'notes'         => ['string'],
            // status_id و password جدا
        ];

        foreach (ClientFormSchema::SYSTEM_FIELDS as $sid => $info) {
            // status_id و password را جداگانه هندل می‌کنیم
            if (in_array($sid, ['status_id', 'password'], true)) {
                continue;
            }

            $def = $schemaFields->firstWhere('id', $sid) ?? ($defaultFields[$sid] ?? null);
            if (!$def) {
                continue;
            }

            $requiredBase   = !empty($def['required']);
            $quickField     = !empty($def['quick_create']);
            $requiredStatus = in_array(
                $targetStatusKey,
                $def['required_status_keys'] ?? [],
                true
            );
            $requiredConditional = $this->isFieldRequiredByConditional($def, $schemaFields->toArray(), $forQuick);

            // در مودال quick اگر نه quick_create و نه required_by_status و نه required_by_conditional، ولیدیت نکن
            if ($forQuick && !$quickField && !$requiredStatus && !$requiredConditional) {
                continue;
            }

            $key    = $forQuick ? "quick.$sid" : $sid;
            $prefix = ($requiredBase || $requiredStatus || $requiredConditional) ? ['required'] : ['nullable'];
            $base   = $baseRules[$sid] ?? [];

            $rules[$key] = array_merge($prefix, $base);
        }

        // ---- status_id ----
        $statusField = $schemaFields->firstWhere('id', 'status_id') ?? ($defaultFields['status_id'] ?? null);
        if ($statusField) {
            $requiredBase   = !empty($statusField['required']);
            $quickField     = !empty($statusField['quick_create']);
            $requiredStatus = in_array(
                $targetStatusKey,
                $statusField['required_status_keys'] ?? [],
                true
            );
            $requiredConditional = $this->isFieldRequiredByConditional($statusField, $schemaFields->toArray(), $forQuick);

            if (!$forQuick || ($forQuick && ($quickField || $requiredStatus || $requiredConditional))) {
                $key    = $forQuick ? 'quick.status_id' : 'status_id';
                $prefix = ($requiredBase || $requiredStatus || $requiredConditional) ? ['required'] : ['nullable'];

                $rules[$key] = array_merge($prefix, ['exists:client_statuses,id']);
            }
        }

        return $rules;
    }

    // 2) ذخیره سریع فقط فیلدهای quick_create
    public function saveQuick()
    {
        try {
            // وضعیت هدف در این ذخیره (اولویت با quick.status_id)
            $targetStatusId  = $this->quick['status_id'] ?? $this->status_id ?? $this->client?->status_id;
            $targetStatusKey = $this->resolveStatusKey($targetStatusId);

            // فیلدهایی که در مودال "ایجاد سریع" فعال‌اند
            $quickFields = collect($this->schema['fields'] ?? [])
                ->where('quick_create', true)
                ->values();

            // قواعد ولیدیشن فیلدهای سیستمی با توجه به فرم‌ساز + وضعیت هدف
            $rules = $this->buildSystemValidationRules(true, $targetStatusKey); // forQuick = true → quick.*

            // قواعد برای فیلدهای داینامیک غیر سیستمی در مودال سریع
            foreach ($quickFields as $f) {
                $fid = $f['id'] ?? null;
                if (!$fid) {
                    continue;
                }

                // فیلدهای سیستمی را اینجا چک نکن
                if (array_key_exists($fid, ClientFormSchema::SYSTEM_FIELDS)) {
                    continue;
                }

                // status هم سیستمی است
                if (($f['type'] ?? null) === 'status') {
                    continue;
                }

                $key = "quick.$fid";

                $statusKeys       = (array)($f['required_status_keys'] ?? []);
                $requiredByStatus = $targetStatusKey && in_array($targetStatusKey, $statusKeys, true);
                $requiredByConditional = $this->isFieldRequiredByConditional($f, $quickFields->toArray(), true);

                if (!empty($f['validate'])) {
                    $ruleStr = $f['validate'];
                    if (($requiredByStatus || $requiredByConditional) && !str_contains($ruleStr, 'required')) {
                        $ruleStr = 'required|' . $ruleStr;
                    }
                    $rules[$key] = $ruleStr;
                } elseif (!empty($f['required']) || $requiredByStatus || $requiredByConditional) {
                    $rules[$key] = 'required';
                }
            }

            // 🔹 در ایجاد سریع: پسورد optional است؛ اگر وارد شد باید قوی باشد
            $rules['password'] = [
                'nullable',
                'string',
                'min:8',
                // حداقل یک حرف و یک عدد (برای فارسی هم ok)
                'regex:/^(?=.*[A-Za-zآ-ی])(?=.*\d).+$/u',
            ];

            // ولیدیشن روی quick.* + password
            $this->validate($rules);

            // بعد از ولیدیشن، مقادیر سیستمی را از quick به پراپرتی‌های اصلی منتقل کن
            $this->full_name     = $this->quick['full_name']     ?? $this->full_name ?? 'کاربر جدید';
            $this->phone         = $this->quick['phone']         ?? $this->phone;
            $this->email         = $this->quick['email']         ?? $this->email;
            $this->national_code = $this->quick['national_code'] ?? $this->national_code;
            $this->case_number   = $this->quick['case_number']   ?? $this->case_number;
            $this->notes         = $this->quick['notes']         ?? $this->notes;
            $this->status_id     = $this->quick['status_id']     ?? $this->status_id;

            // map از quick به meta برای فیلدهای غیر سیستمی
            foreach ($quickFields as $f) {
                $fid = $f['id'] ?? null;
                if (!$fid) {
                    continue;
                }

                if (array_key_exists($fid, ClientFormSchema::SYSTEM_FIELDS)) {
                    continue;
                }

                if (($f['type'] ?? null) === 'status') {
                    continue;
                }

                $this->meta[$fid] = $this->quick[$fid] ?? null;
            }

            // حالت quick را فعال کن
            $this->isQuickMode = true;

            return $this->save();
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[Clients] saveQuick failed', ['msg' => $e->getMessage()]);
            $this->dispatch('notify', type: 'error', text: 'خطا در ایجاد سریع.');
            throw $e;
        } finally {
            $this->isQuickMode = false;
        }
    }

    // 3) ذخیره کامل (ایجاد/ویرایش)
    public function save()
    {
        $targetStatusId  = $this->status_id ?? $this->client?->status_id;
        $targetStatusKey = $this->resolveStatusKey($targetStatusId);

        $rules = $this->buildSystemValidationRules(false, $targetStatusKey);

        $schemaFields   = collect($this->schema['fields'] ?? []);
        $passwordField  = $schemaFields->firstWhere('id', 'password');
        $requiredBase   = !empty($passwordField['required'] ?? false);

        $isCreating     = !($this->client && $this->client->exists);

        $mustBeRequired = $isCreating && $requiredBase && !$this->auto_generate_password;

        $passwordRulePrefix = $mustBeRequired ? ['required'] : ['nullable'];

        $rules['password'] = array_merge(
            $passwordRulePrefix,
            [
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-zآ-ی])(?=.*\d).+$/u',
            ]
        );

        foreach ($this->schema['fields'] as $f) {
            $fid = $f['id'] ?? null;
            if (!$fid) {
                continue;
            }

            if (array_key_exists($fid, ClientFormSchema::SYSTEM_FIELDS)) {
                continue;
            }

            if (($f['type'] ?? null) === 'status') {
                continue;
            }

            $key = "meta.$fid";

            $statusKeys       = (array)($f['required_status_keys'] ?? []);
            $requiredByStatus = $targetStatusKey && in_array($targetStatusKey, $statusKeys, true);
            $requiredByConditional = $this->isFieldRequiredByConditional($f, $this->schema['fields'] ?? [], false);

            if (!empty($f['validate'])) {
                $ruleStr = $f['validate'];
                if (($requiredByStatus || $requiredByConditional || !empty($f['required'])) && !str_contains($ruleStr, 'required')) {
                    $ruleStr = 'required|' . $ruleStr;
                }
                $rules[$key] = $ruleStr;
            } elseif (!empty($f['required']) || $requiredByStatus || $requiredByConditional) {
                if ($this->isQuickMode && empty($f['quick_create']) && !$requiredByStatus && !$requiredByConditional) {
                    continue;
                }
                $rules[$key] = 'required';
            }
        }

        $this->validate($rules);

        // تبدیل تاریخ‌های جلالی به میلادی قبل از ذخیره
        $this->meta = $this->convertMetaDatesForStorage($this->meta ?? [], $this->schema);

        foreach (($this->meta ?? []) as $k => $v) {
            if ($v instanceof TemporaryUploadedFile) {
                $this->meta[$k] = $this->uploadFile($v, 'clients/uploads', 'public');
            } elseif (is_array($v)) {
                $files = [];
                $hasUploadedFiles = false;
                foreach ($v as $subV) {
                    if ($subV instanceof TemporaryUploadedFile) {
                        $files[] = $this->uploadFile($subV, 'clients/uploads', 'public');
                        $hasUploadedFiles = true;
                    } else {
                        $files[] = $subV;
                    }
                }
                if ($hasUploadedFiles) {
                    $this->meta[$k] = $files;
                }
            }
            // مقادیر JSON string (مثل select-province-city) به صورت string نگه داشته می‌شوند
            // و در meta به صورت JSON ذخیره می‌شوند
        }

        if ($this->client && $this->client->exists) {
            $this->username = $this->client->username ?: $this->generateUsernameFromSettings();
        } else {
            $this->username = $this->generateUsernameFromSettings();
        }

        $strategy = ClientSetting::getValue('username_strategy')
            ?: config('clients.username.strategy', 'email_local');

        if (in_array($strategy, ['email', 'mobile', 'national_code'], true) && empty($this->username)) {
            $this->addError('username', 'امکان ساخت یوزرنیم بر اساس استراتژی انتخاب‌شده وجود ندارد (ایمیل/موبایل/کدملی ناقص است).');
            $this->dispatch('notify', type: 'error', text: 'ایمیل/موبایل/کدملی برای ساخت یوزرنیم کافی نیست.');
            return;
        }

        if (in_array($strategy, ['email', 'mobile', 'national_code'], true)) {
            $existsQuery = Client::query()->where('username', $this->username);

            if ($this->client && $this->client->exists) {
                $existsQuery->where('id', '!=', $this->client->id);
            }

            if ($existsQuery->exists()) {
                $this->addError('username', 'این یوزرنیم قبلاً استفاده شده است.');
                $this->dispatch('notify', type: 'error', text: 'یوزرنیم انتخاب‌شده (بر اساس ایمیل/موبایل/کدملی) قبلاً استفاده شده است.');
                return;
            }
        }

        $plainPassword = null;

        if ($this->client && $this->client->exists) {
            if (!empty($this->password)) {
                $plainPassword = $this->password;
            }
        } else {
            if (!empty($this->password)) {
                $plainPassword = $this->password;
            } elseif ($this->auto_generate_password) {
                $plainPassword = Str::random(12);
                $this->password = $plainPassword;
                $this->password_confirmation = $plainPassword;
            }
        }

        $payload = [
            'username'      => $this->username,
            'full_name'     => $this->full_name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'national_code' => $this->national_code,
            'case_number'   => $this->case_number,
            'notes'         => $this->notes,
            'status_id'     => $this->status_id,
            'meta'          => $this->meta ?? [],
            'created_by'    => Auth::id(),
        ];

        if (!empty($plainPassword)) {
            $payload['password'] = bcrypt($plainPassword);
        }

        DB::beginTransaction();
        try {
            $isNew = false;

            if ($this->client && $this->client->exists) {
                $this->client->fill($payload);
                $ok = $this->client->save();
                Log::info('[Clients] update result', ['ok' => $ok, 'id' => $this->client->id]);
                $client = $this->client;
            } else {
                $client = Client::create($payload);
                $this->client = $client;
                $isNew = true;
                Log::info('[Clients] create result', ['id' => $client?->id]);
            }

            // Save new dynamic select options globally if configured
            app(\Modules\Clients\App\Services\ClientFormService::class)->saveNewOptionsFromPayload($this->meta ?? []);

            // هماهنگ‌سازی رابطه‌های چندبه‌چند کاربران بر اساس نقش‌ها
            $allUserIdsToSync = [];
            foreach ($this->schema['fields'] as $f) {
                if (($f['type'] ?? null) === 'select-user-by-role' && !empty($f['role'])) {
                    $val = data_get($this->meta, $f['id']);
                    $ids = is_array($val) ? $val : (empty($val) ? [] : [$val]);
                    $ids = array_filter(array_map('intval', $ids));

                    if (!empty($f['lock_current_if_role']) && Auth::user()?->hasRole($f['role'])) {
                        $ids = [Auth::id()];
                    }
                    
                    // اضافه کردن به لیست کلی برای سینک نهایی
                    foreach ($ids as $id) {
                        $allUserIdsToSync[$id] = true;
                    }
                }
            }
            
            // اگر فیلد نقش‌داری وجود دارد، سینک را بر اساس آنها انجام دهیم.
            // برای اینکه کاربرانی که قبلاً متصل بودند و ربطی به نقش‌های فرم‌ساز فعلی ندارند پاک نشوند،
            // فقط کاربرانی که نقشِ فیلدهای فعلی فرم‌ساز را دارند مدیریت (سینک یا دتاچ) می‌کنیم.
            $rolesInForm = collect($this->schema['fields'])
                ->where('type', 'select-user-by-role')
                ->pluck('role')
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($rolesInForm)) {
                // پیدا کردن آی‌دی تمام کاربرانی که این نقش‌ها را دارند تا بدانیم کدام‌ها باید حذف/اضافه شوند
                $allUsersWithFormRoles = User::role($rolesInForm)->pluck('id')->toArray();
                
                // لیست کاربرانی که در حال حاضر به کلاینت متصل هستند
                $currentAttachedUserIds = $client->users()->pluck('users.id')->toArray();
                
                // کاربرانی که نقشی غیر از نقش‌های این فرم دارند را حفظ می‌کنیم
                $userIdsToKeep = array_diff($currentAttachedUserIds, $allUsersWithFormRoles);
                
                // ترکیب کاربرانی که باید حفظ شوند با کاربران جدید انتخاب‌شده در فرم
                $finalSyncIds = array_unique(array_merge($userIdsToKeep, array_keys($allUserIdsToSync)));
                
                $client->users()->sync($finalSyncIds);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Clients] persist failed', ['msg' => $e->getMessage()]);
            $this->dispatch('notify', type: 'error', text: 'خطا در ذخیره‌سازی.');
            throw $e;
        }

        $this->dispatch('notify', type: 'success', text: $isNew ? 'ایجاد شد.' : 'به‌روزرسانی شد.');

        if (!empty($plainPassword)) {
            $this->dispatch(
                'client-password-created',
                username: $this->username,
                password: $plainPassword
            );
        }

        // ✅ در حالت ایجاد سریع: فقط مودال quick بسته شود، بدون ریدایرکت
        if ($this->isQuickMode) {
            $this->dispatch('client-quick-saved', clientId: $client->id, clientName: $client->full_name);

            // اگر ویجت داشبورد است، فیلدها را پاک کن تا برای ثبت بعدی آماده شود
            if ($this->forWidget) {
                $this->reset(['quick', 'full_name', 'phone', 'email', 'national_code', 'case_number', 'notes', 'status_id', 'meta', 'password', 'password_confirmation', 'auto_generate_password']);
                $this->client = new Client();

                // بازنشانی وضعیت پیش‌فرض برای مشتری جدید بعدی
                $firstStatus = ClientStatus::active()->first();
                $this->status_id = $firstStatus ? $firstStatus->id : null;

                // اضافه شده: بازنشانی وضعیت پیش‌فرض برای فرم quick
                if ($this->status_id) {
                    $this->quick['status_id'] = $this->status_id;
                }
            }

            return;
        }

        // ✅ در ایجاد معمولی + پسورد: در همین صفحه بمان تا مودال پسورد نمایش داده شود
        if (!empty($plainPassword)) {
            return;
        }

        // در بقیه حالت‌ها: ریدایرکت به لیست
        return redirect()->route('user.clients.index');
    }

    // === ژنراتور یوزرنیم یکتا بر اساس تنظیمات ===
    private function generateUsernameFromSettings(): string
    {
        $strategy = ClientSetting::getValue('username_strategy')
            ?: config('clients.username.strategy', 'email_local');

        $prefix = ClientSetting::getValue('username_prefix', 'clt');
        $minLen = 3;

        $existsInClients = fn(string $u) =>
        DB::table('clients')->where('username', $u)->exists();

        $candidate = null;

        switch ($strategy) {
            case 'email':
                $candidate = (string) $this->email;
                break;

            case 'national_code':
                $candidate = (string) $this->national_code;
                break;

            case 'mobile':
                $digits = preg_replace('/\D+/', '', (string) $this->phone);
                $candidate = $digits ?: null;
                if (!$candidate || strlen($candidate) < 8) {
                    $candidate = null;
                }
                break;

            case 'name_increment':
                $base = \Illuminate\Support\Str::slug((string) $this->full_name);
                if (!$base || strlen($base) < $minLen) {
                    $base = \Illuminate\Support\Str::slug(
                        (string) \Illuminate\Support\Str::before((string)$this->email, '@')
                    ) ?: 'user';
                }
                $candidate = $this->incrementUsernameBase($base, $existsInClients);
                break;

            case 'prefix_increment':
                $last = DB::table('clients')
                    ->where('username', 'like', "{$prefix}-%")
                    ->selectRaw("MAX(CAST(SUBSTRING_INDEX(username, '-', -1) AS UNSIGNED)) as mx")
                    ->value('mx');
                $next = (int)$last + 1;
                $candidate = sprintf('%s-%04d', $prefix, $next);
                break;

            case 'email_local':
            default:
                $local = (string) \Illuminate\Support\Str::before((string)$this->email, '@');
                $base  = \Illuminate\Support\Str::slug($local ?: (string)$this->full_name) ?: 'user';
                $candidate = $this->incrementUsernameBase($base, $existsInClients);
                break;
        }

        if (in_array($strategy, ['email', 'mobile', 'national_code'], true)) {
            Log::info('[Clients] username candidate (strict) ', [
                'strategy'  => $strategy,
                'candidate' => $candidate,
            ]);
            return (string) $candidate;
        }

        if ($existsInClients($candidate)) {
            $candidate = $this->incrementUsernameBase($candidate, $existsInClients);
        }

        Log::info('[Clients] username candidate (auto-unique)', [
            'strategy'  => $strategy,
            'candidate' => $candidate,
        ]);

        return (string) $candidate;
    }

    private function incrementUsernameBase(string $base, \Closure $exists): string
    {
        $base = trim($base) ?: 'user';
        if (!$exists($base)) return $base;

        $i = 1;
        while ($exists($base . $i)) $i++;
        return $base . $i;
    }

    private function incrementUsername(string $base): string
    {
        $base = trim($base) ?: 'user';

        $existsInClients = fn($u) => DB::table('clients')->where('username', $u)->exists();
        $u = $base;
        if (!$existsInClients($u)) return $u;

        $i = 1;
        while ($existsInClients($base . $i)) $i++;
        return $base . $i;
    }

    /**
     * تبدیل تاریخ‌های میلادی به جلالی برای نمایش در فرم
     */
    private function convertMetaDatesForDisplay(array $meta, ?ClientFormSchema $form): array
    {
        if (!$form) {
            return $meta;
        }

        $fields = $form->schema['fields'] ?? [];
        $dateFields = [];

        // پیدا کردن فیلدهای تاریخ
        foreach ($fields as $field) {
            if (($field['type'] ?? null) === 'date') {
                $fid = $field['id'] ?? null;
                if ($fid) {
                    $dateFields[] = $fid;
                }
            }
        }

        if (empty($dateFields)) {
            return $meta;
        }

        foreach ($dateFields as $fid) {
            if (!isset($meta[$fid]) || empty($meta[$fid])) {
                continue;
            }

            $value = $meta[$fid];

            // اگر مقدار به صورت Y-m-d (میلادی) است، به جلالی تبدیل کن
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                try {
                    $carbon = Carbon::createFromFormat('Y-m-d', $value);
                    [$jy, $jm, $jd] = CalendarUtils::toJalali(
                        $carbon->year,
                        $carbon->month,
                        $carbon->day
                    );
                    $meta[$fid] = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
                } catch (\Throwable $e) {
                    Log::warning('[Clients] Failed to convert date for display', [
                        'field' => $fid,
                        'value' => $value,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $meta;
    }

    /**
     * تبدیل تاریخ‌های جلالی به میلادی برای ذخیره در دیتابیس
     */
    private function convertMetaDatesForStorage(array $meta, array $schema): array
    {
        $fields = $schema['fields'] ?? [];
        $dateFields = [];

        // پیدا کردن فیلدهای تاریخ
        foreach ($fields as $field) {
            if (($field['type'] ?? null) === 'date') {
                $fid = $field['id'] ?? null;
                if ($fid) {
                    $dateFields[] = $fid;
                }
            }
        }

        if (empty($dateFields)) {
            return $meta;
        }

        foreach ($dateFields as $fid) {
            if (!isset($meta[$fid]) || empty($meta[$fid])) {
                continue;
            }

            $value = trim($meta[$fid]);

            // اگر مقدار به صورت Y/m/d (جلالی) است، به میلادی تبدیل کن
            // فرمت جلالی معمولاً 1403/09/15 است
            if (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $value)) {
                try {
                    // نرمال‌سازی ارقام فارسی به انگلیسی
                    $value = $this->normalizeJalaliDigits($value);

                    $parts = preg_split('/[^\d]+/', $value);
                    if (count($parts) >= 3) {
                        [$jy, $jm, $jd] = array_map('intval', array_slice($parts, 0, 3));
                        [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);
                        $meta[$fid] = sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
                    }
                } catch (\Throwable $e) {
                    Log::warning('[Clients] Failed to convert Jalali date for storage', [
                        'field' => $fid,
                        'value' => $value,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $meta;
    }

    /**
     * تبدیل ارقام فارسی/عربی به انگلیسی
     */
    private function normalizeJalaliDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($persian, $latin, $value);
    }

    /**
     * بررسی اینکه آیا یک فیلد به صورت شرطی الزامی است (برای نمایش در UI)
     */
    public function isFieldConditionallyRequired(string $fieldId): bool
    {
        $field = collect($this->schema['fields'] ?? [])->firstWhere('id', $fieldId);
        if (!$field) {
            return false;
        }

        return $this->isFieldRequiredByConditional($field, $this->schema['fields'] ?? [], false);
    }

    /**
     * حذف فایل پویا
     */
    public function deleteDynamicFile(string $fid, int $index): void
    {
        $existing = $this->meta[$fid] ?? null;
        if (!$existing) {
            return;
        }

        $existingFiles = [];
        if (is_array($existing)) {
            $existingFiles = $existing;
        } else {
            $decoded = json_decode($existing, true);
            if (is_array($decoded)) {
                $existingFiles = $decoded;
            } else {
                $existingFiles = [$existing];
            }
        }

        if (isset($existingFiles[$index])) {
            $filePath = $existingFiles[$index];
            Storage::disk('public')->delete($filePath);
            unset($existingFiles[$index]);
            $existingFiles = array_values($existingFiles);
        }

        if (empty($existingFiles)) {
            unset($this->meta[$fid]);
        } else {
            $field = collect($this->schema['fields'] ?? [])->firstWhere('id', $fid);
            $multiple = !empty($field['multiple']);
            if ($multiple) {
                $this->meta[$fid] = $existingFiles;
            } else {
                $this->meta[$fid] = $existingFiles[0] ?? null;
            }
        }
    }

    /**
     * همگام‌سازی و ادغام فایل‌های جدید و قدیم هنگام آپلود پویا
     */
    public function updatedUploadFiles($value, $key): void
    {
        $fid = $key;
        $field = collect($this->schema['fields'] ?? [])->firstWhere('id', $fid);
        $multiple = $field && !empty($field['multiple']);

        if ($multiple) {
            $existing = $this->meta[$fid] ?? [];
            if (!is_array($existing)) {
                $decoded = json_decode($existing, true);
                $existing = is_array($decoded) ? $decoded : ($existing ? [$existing] : []);
            }
            
            $newFiles = is_array($value) ? $value : [$value];
            
            // ادغام فایل‌های قدیمی با فایل‌های آپلود شده جدید
            $this->meta[$fid] = array_merge($existing, $newFiles);
        } else {
            // آپلود تکی
            $this->meta[$fid] = is_array($value) ? ($value[0] ?? null) : $value;
        }

        // ریست کردن ویژگی مربوط به آپلود
        unset($this->upload_files[$fid]);
    }
}
