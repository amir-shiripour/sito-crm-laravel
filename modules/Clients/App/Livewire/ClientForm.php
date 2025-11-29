<?php

namespace Modules\Clients\App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Modules\Clients\Entities\ClientStatus;

use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientForm as ClientFormSchema;
use Modules\Clients\Entities\ClientSetting;
use App\Models\User;

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

    public array $meta = [];
    public $status_id = null;

    // اسکیمای فرم پویا (از ClientFormSchema)
    public array $schema = ['fields' => []];

    // 1) استیت ایجاد سریع
    public array $quick = [];
    public array $availableStatuses = [];

    public function mount(?Client $client = null, ?string $formKey = null)
    {
        $this->client = $client;

        // انتخاب فرم فعال: تنظیمات → default → آخرین
        $keyFromSettings = ClientSetting::getValue('default_form_key');
        $form = $formKey
            ? ClientFormSchema::where('key', $formKey)->first()
            : ClientFormSchema::active($keyFromSettings);

        $this->schema = $form?->schema ?? ['fields' => []];

        $currentStatusId = $client?->status_id;

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
        } else {
            $this->username      = null;
            $this->full_name     = '';
            $this->email         = null;
            $this->phone         = null;
            $this->national_code = null;
            $this->notes         = null;
            $this->meta          = [];
            $this->status_id     = null;
        }

    }

    public bool $asQuickWidget = false;
    public bool $isQuickMode  = false;

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

    // 2) ذخیره سریع فقط فیلدهای quick_create
    public function saveQuick()
    {
        try {
            // 1) پر کردن فیلدهای سیستمی از quick (اگر در فرم سریع وجود داشته باشند)
            $this->full_name     = $this->full_name ?: ($this->quick['full_name'] ?? 'کاربر جدید');
            $this->phone         = $this->quick['phone']         ?? $this->phone;
            $this->email         = $this->quick['email']         ?? $this->email;
            $this->national_code = $this->quick['national_code'] ?? $this->national_code;
            $this->notes         = $this->quick['notes']         ?? $this->notes;
            $this->status_id     = $this->quick['status_id']     ?? $this->status_id;

            // 2) فیلدهایی که در مودال "ایجاد سریع" فعال‌اند
            $quickFields = collect($this->schema['fields'] ?? [])
                ->where('quick_create', true)
                ->values();

            // قواعد ولیدیشن برای حالت ایجاد سریع
            $rules = [
                'full_name'     => ['required','string','max:255'],
                'email'         => ['nullable','email'],
                'phone'         => ['nullable','string'],
                'national_code' => ['nullable','string','max:20'],
                'notes'         => ['nullable','string'],
                'quick.status_id' => ['nullable', 'exists:client_statuses,id'],
            ];

            // قواعد برای فیلدهای داینامیک (غیر سیستمی) در مودال سریع
            foreach ($quickFields as $f) {
                $fid = $f['id'] ?? null;
                if (!$fid) {
                    continue;
                }

                // فیلدهای سیستمی → اینجا ولیدیت نمی‌شوند (قبلاً بالا rule دارند)
                if (array_key_exists($fid, ClientFormSchema::SYSTEM_FIELDS)) {
                    continue;
                }

                // فیلد نوع status → rule آن بالا تعریف شده
                if (($f['type'] ?? null) === 'status') {
                    continue;
                }

                $key = "quick.$fid";

                if (!empty($f['validate'])) {
                    $rules[$key] = $f['validate'];
                } elseif (!empty($f['required'])) {
                    $rules[$key] = 'required';
                }
            }

            // 3) ولیدیشن روی quick.* + full_name/email/...
            $this->validate($rules);

            // 4) map از quick به meta برای فیلدهای غیر سیستمی
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

            // 5) مقدار نهایی status_id از quick (اگر بود)
            $this->status_id = $this->quick['status_id'] ?? $this->status_id;

            // 6) حالت quick را فعال کن و از مسیر اصلی ذخیره استفاده کن
            $this->isQuickMode = true;

            return $this->save();

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[Clients] saveQuick failed', ['msg' => $e->getMessage()]);
            $this->dispatch('notify', type: 'error', text: 'خطا در ایجاد سریع.');
            throw $e;
        } finally {
            // بعد از پایان، چه موفق چه خطا، فلگ را ریست کن
            $this->isQuickMode = false;
        }
    }



    // 3) ذخیره کامل (ایجاد/ویرایش) — نسخه نهایی
    public function save()
    {
        $rules = [
            //            'username'  => ['required','string','max:191', Rule::unique('clients','username')->ignore($this->client?->id)],
            'full_name'     => ['required','string','max:255'],
            'email'         => ['nullable','email'],
            'phone'         => ['nullable','string'],
            'national_code' => ['nullable','string','max:20'],
            'notes'         => ['nullable','string'],
            'status_id'     => ['nullable', 'exists:client_statuses,id'],
        ];

        foreach ($this->schema['fields'] as $f) {
            $fid = $f['id'] ?? null;
            if (!$fid) {
                continue;
            }

            // اگر این آیدی جزو فیلدهای سیستمی است، اینجا ولیدیتش نکن
            if (array_key_exists($fid, ClientFormSchema::SYSTEM_FIELDS)) {
                continue;
            }

            // اگر نوعش status باشد، باز هم اینجا کاری نکن
            if (($f['type'] ?? null) === 'status') {
                continue;
            }

            $key = "meta.$fid";

            if (!empty($f['validate'])) {
                $rules[$key] = $f['validate'];
            } elseif (!empty($f['required'])) {

                // 🚩 اینجا جادوی quick-mode:
                // اگر در حالت ایجاد سریع هستیم و این فیلد quick_create=false است،
                // پس در quick نباید مجبور به پر کردنش باشیم.
                if ($this->isQuickMode && empty($f['quick_create'])) {
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

        // اطمینان از داشتن username (و نگه‌داشت در meta)
        if ($this->client && $this->client->exists) {
            $this->username = $this->client->username ?: $this->generateUsernameFromSettings();
        } else {
            $this->username = $this->generateUsernameFromSettings();
        }
//        $this->meta['username'] = $this->username;

        $strategy = ClientSetting::getValue('username_strategy')
            ?: config('clients.username.strategy', 'email_local');

        // اگر استراتژی strict است و username خالی دراومده → ارور
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

        DB::beginTransaction();
        try {
            if ($this->client && $this->client->exists) {
                $this->client->fill($payload);
                $ok = $this->client->save();
                Log::info('[Clients] update result', ['ok' => $ok, 'id' => $this->client->id]);
                $client = $this->client;
            } else {
                $client = Client::create($payload);
                Log::info('[Clients] create result', ['id' => $client?->id]);
            }

            // سنک نقش‌محور
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

        $this->dispatch('notify', type: 'success', text: $this->client ? 'به‌روزرسانی شد.' : 'ایجاد شد.');
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
            case 'email': // کل ایمیل
                $candidate = (string) $this->email;
                break;

            case 'national_code': // کدملی
                $candidate = (string) $this->national_code;
                break;

            case 'mobile': // فقط ارقام موبایل
                $digits = preg_replace('/\D+/', '', (string) $this->phone);
                $candidate = $digits ?: null;
                if (!$candidate || strlen($candidate) < 8) {
                    // اگر موبایل درست نبود، یک base حداقلی برای پیام خطا یا fallback
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

        // ⚠️ اینجاست که رفتار ویژه را اعمال می‌کنیم:
        if (in_array($strategy, ['email', 'mobile', 'national_code'], true)) {
            // برای این دو حالت، فقط همون candidate رو برمی‌گردونیم
            // (چک یکتا در متد save انجام می‌شود و اگر تکراری بود، خطا می‌دهیم)
            Log::info('[Clients] username candidate (strict) ', [
                'strategy'  => $strategy,
                'candidate' => $candidate,
            ]);
            return (string) $candidate;
        }

        // برای بقیه‌ی استراتژی‌ها، مثل قبل auto-increment کن
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

        // همه‌ی usernameهای مشابه در clients (و دلخواه users):
        $pattern = '^'.preg_quote($base).'(?:([0-9]+))?$';

        $existsInClients = fn($u) => DB::table('clients')->where('username',$u)->exists();
        $u = $base;
        if (!$existsInClients($u)) return $u;

        $i = 1;
        while ($existsInClients($base.$i)) $i++;
        return $base.$i;
    }
}
