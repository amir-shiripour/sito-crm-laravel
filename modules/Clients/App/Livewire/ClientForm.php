<?php

namespace Modules\Clients\App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Clients\Entities\ClientStatus;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientForm as ClientFormSchema;
use Modules\Clients\Entities\ClientSetting;
use App\Models\User;
use Illuminate\Support\Str;

#[Layout('layouts.user')]
class ClientForm extends Component
{
    // بایندهای استاندارد فرم
    public ?Client $client = null;

    public ?string $username = null;
    public string $full_name = '';

    public ?string $email = null;
    public ?string $phone = null;
    public ?string $national_code = null;
    public ?string $notes = null;

    // 🔹 فیلدهای مربوط به ورود کلاینت
    public ?string $password = null;
    public ?string $password_confirmation = null; // برای auto-generate فقط
    public bool $auto_generate_password = false;

    public array $meta = [];
    public $status_id = null;

    // اسکیمای فرم پویا (از ClientFormSchema)
    public array $schema = ['fields' => []];

    // استیت ایجاد سریع
    public array $quick = [];
    public array $availableStatuses = [];

    public bool $asQuickWidget = false;
    public bool $isQuickMode   = false;

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

    public function mount(?Client $client = null, ?string $formKey = null)
    {
        $this->client = $client;

        // انتخاب فرم فعال: تنظیمات → default → آخرین
        $keyFromSettings = ClientSetting::getValue('default_form_key');
        $form = $formKey
            ? ClientFormSchema::where('key', $formKey)->first()
            : ClientFormSchema::active($keyFromSettings);

        $this->schema = $form?->schema ?? ['fields' => []];

        // وضعیت‌های فعال
        $statuses = ClientStatus::active()->get();

        $currentStatusId  = $client?->status_id;
        $currentStatusKey = optional($client?->status)->key;

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

        if ($client) {
            $this->username      = $client->username;
            $this->full_name     = (string) $client->full_name;
            $this->email         = $client->email;
            $this->phone         = $client->phone;
            $this->national_code = $client->national_code;
            $this->notes         = $client->notes;
            $this->meta          = $client->meta ?? [];
            $this->status_id     = $client->status_id;

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
            $this->notes         = null;
            $this->meta          = [];
            $this->status_id     = null;
            $this->password      = null;
            $this->password_confirmation = null;
            $this->auto_generate_password = false;
        }
    }

    public function render()
    {
        return $this->asQuickWidget
            ? view('clients::user.clients.quick-widget')
            : view('clients::user.clients.dynamic-form');
    }

    // Helper برای select-user-by-role
    public function usersForRole(?string $role)
    {
        if (!$role) return collect();
        return User::role($role)->select('id','name')->orderBy('name')->get();
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
     * ساخت قوانین ولیدیشن برای فیلدهای سیستمی
     */
    private function buildSystemValidationRules(bool $forQuick = false, ?string $targetStatusKey = null): array
    {
        $rules         = [];
        $schemaFields  = collect($this->schema['fields'] ?? []);
        $defaultFields = ClientFormSchema::systemFieldDefaults();

        // رول‌های پایه برای هر فیلد سیستمی
        $baseRules = [
            'full_name'     => ['string','max:255'],
            'phone'         => ['string'],
            'email'         => ['email'],
            'national_code' => ['string','max:20'],
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

            // در مودال quick اگر نه quick_create و نه required_by_status، ولیدیت نکن
            if ($forQuick && !$quickField && !$requiredStatus) {
                continue;
            }

            $key    = $forQuick ? "quick.$sid" : $sid;
            $prefix = ($requiredBase || $requiredStatus) ? ['required'] : ['nullable'];
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

            if (!$forQuick || ($forQuick && ($quickField || $requiredStatus))) {
                $key    = $forQuick ? 'quick.status_id' : 'status_id';
                $prefix = ($requiredBase || $requiredStatus) ? ['required'] : ['nullable'];

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

                if (!empty($f['validate'])) {
                    $ruleStr = $f['validate'];
                    if ($requiredByStatus && !str_contains($ruleStr, 'required')) {
                        $ruleStr = 'required|' . $ruleStr;
                    }
                    $rules[$key] = $ruleStr;
                } elseif (!empty($f['required']) || $requiredByStatus) {
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

            if (!empty($f['validate'])) {
                $ruleStr = $f['validate'];
                if (($requiredByStatus || !empty($f['required'])) && !str_contains($ruleStr, 'required')) {
                    $ruleStr = 'required|' . $ruleStr;
                }
                $rules[$key] = $ruleStr;
            } elseif (!empty($f['required']) || $requiredByStatus) {
                if ($this->isQuickMode && empty($f['quick_create']) && !$requiredByStatus) {
                    continue;
                }
                $rules[$key] = 'required';
            }
        }

        $this->validate($rules);

        foreach (($this->meta ?? []) as $k => $v) {
            if ($v instanceof TemporaryUploadedFile) {
                $this->meta[$k] = $v->store('clients/uploads', 'public');
            }
        }

        if ($this->client && $this->client->exists) {
            $this->username = $this->client->username ?: $this->generateUsernameFromSettings();
        } else {
            $this->username = $this->generateUsernameFromSettings();
        }

        $strategy = ClientSetting::getValue('username_strategy')
            ?: config('clients.username.strategy', 'email_local');

        if (in_array($strategy, ['email','mobile','national_code'], true) && empty($this->username)) {
            $this->addError('username', 'امکان ساخت یوزرنیم بر اساس استراتژی انتخاب‌شده وجود ندارد (ایمیل/موبایل/کدملی ناقص است).');
            $this->dispatch('notify', type: 'error', text: 'ایمیل/موبایل/کدملی برای ساخت یوزرنیم کافی نیست.');
            return;
        }

        if (in_array($strategy, ['email','mobile','national_code'], true)) {
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

            foreach ($this->schema['fields'] as $f) {
                if (($f['type'] ?? null) === 'select-user-by-role' && !empty($f['role'])) {
                    $val = data_get($this->meta, $f['id']);
                    $ids = is_array($val) ? $val : (empty($val) ? [] : [$val]);

                    if (!empty($f['lock_current_if_role']) && Auth::user()?->hasRole($f['role'])) {
                        $ids = [Auth::id()];
                    }
                    $client->users()->syncWithoutDetaching($ids);
                }
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
            $this->dispatch('client-quick-saved');
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

        $existsInClients = fn (string $u) =>
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
                    ->where('username','like', "{$prefix}-%")
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
        while ($exists($base.$i)) $i++;
        return $base.$i;
    }

    private function incrementUsername(string $base): string
    {
        $base = trim($base) ?: 'user';

        $existsInClients = fn($u) => DB::table('clients')->where('username',$u)->exists();
        $u = $base;
        if (!$existsInClients($u)) return $u;

        $i = 1;
        while ($existsInClients($base.$i)) $i++;
        return $base.$i;
    }
}
