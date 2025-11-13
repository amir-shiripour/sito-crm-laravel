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
    public ?string $notes = null;

    public array $meta = [];

    // اسکیمای فرم پویا (از ClientFormSchema)
    public array $schema = ['fields' => []];

    // 1) استیت ایجاد سریع
    public array $quick = [];

    public function mount(?Client $client = null, ?string $formKey = null)
    {
        $this->client = $client;

        // انتخاب فرم فعال: تنظیمات → default → آخرین
        $keyFromSettings = ClientSetting::getValue('default_form_key');
        $form = $formKey
            ? ClientFormSchema::where('key', $formKey)->first()
            : ClientFormSchema::active($keyFromSettings);

        $this->schema = $form?->schema ?? ['fields' => []];

        if ($client) {
            $this->username  = $client->username;
            $this->full_name = (string) $client->full_name;
            $this->email     = $client->email;
            $this->phone     = $client->phone;
            $this->notes     = $client->notes;
            $this->meta      = $client->meta ?? [];
        }
    }

    public bool $asQuickWidget = false;

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
        $this->full_name = $this->full_name ?: ($this->quick['full_name'] ?? 'کاربر جدید');
        $this->username  = $this->generateUsernameFromSettings();

        $quickFields = collect($this->schema['fields'] ?? [])
            ->where('quick_create', true)->values();

        $rules = [
//            'username'  => ['required','string','max:191', Rule::unique('clients','username')->ignore($this->client?->id)],
            'full_name' => ['required','string','max:255'],
            'email'     => ['nullable','email'],
            'phone'     => ['nullable','string'],
            'notes'     => ['nullable','string'],
        ];
        foreach ($quickFields as $f) {
            $key = "quick.{$f['id']}";
            if (!empty($f['validate']))     $rules[$key] = $f['validate'];
            elseif (!empty($f['required'])) $rules[$key] = 'required';
        }
        $this->validate($rules); // Rule::unique + ignore(id) طبق داک. :contentReference[oaicite:1]{index=1}

        // map به meta از quick
        foreach ($quickFields as $f) {
            $fid = $f['id'];
            $this->meta[$fid] = $this->quick[$fid] ?? null;
        }

        // در meta هم نگه‌دار (اختیاری)
//        $this->meta['username'] = $this->username;

        $this->dispatch('notify', type: 'success', text: 'ایجاد سریع با موفقیت انجام شد.');

        return $this->save();
    }

    // 3) ذخیره کامل (ایجاد/ویرایش) — نسخه نهایی
    public function save()
    {
        $rules = [
//            'username'  => ['required','string','max:191', Rule::unique('clients','username')->ignore($this->client?->id)],
            'full_name' => ['required','string','max:255'],
            'email'     => ['nullable','email'],
            'phone'     => ['nullable','string'],
            'notes'     => ['nullable','string'],
        ];
        foreach ($this->schema['fields'] as $f) {
            $key = "meta.{$f['id']}";
            if (!empty($f['validate']))     $rules[$key] = $f['validate'];
            elseif (!empty($f['required'])) $rules[$key] = 'required';
        }
        $this->validate($rules);

        foreach (($this->meta ?? []) as $k => $v) {
            if ($v instanceof TemporaryUploadedFile) {
                $this->meta[$k] = $v->store('clients/uploads', 'public'); // Livewire uploads v3. :contentReference[oaicite:3]{index=3}
            }
        }

        // اطمینان از داشتن username (و نگه‌داشت در meta)
        if ($this->client && $this->client->exists) {
            // مسیر ویرایش
            $this->username = $this->client->username ?: $this->generateUsernameFromSettings();
        } else {
            // مسیر ایجاد
            $this->username = $this->generateUsernameFromSettings();
        }
//        $this->meta['username'] = $this->username;

        $strategy = ClientSetting::getValue('username_strategy')
            ?: config('clients.username.strategy', 'email_local');

        if (in_array($strategy, ['email', 'mobile'], true)) {
            $existsQuery = Client::query()->where('username', $this->username);

            if ($this->client && $this->client->exists) {
                // در حالت ویرایش رکورد فعلی را نادیده بگیر
                $existsQuery->where('id', '!=', $this->client->id);
            }

            if ($existsQuery->exists()) {
                // Livewire ولیدیشن error برای نمایش کنار فرم
                $this->addError('username', 'این یوزرنیم قبلاً استفاده شده است.');

                // نوتیفیکیشن Alpine (toast) که قبلاً پیاده کرده‌ای
                $this->dispatch('notify', type: 'error', text: 'یوزرنیم انتخاب‌شده (بر اساس ایمیل/موبایل) قبلاً استفاده شده است.');

                // تراکنش را شروع نکردیم هنوز، پس همین‌جا برگرد
                return;
            }
        }

        $payload = [
            'username'   => $this->username,   // ← مهم
            'full_name'  => $this->full_name,  // ← مهم
            'email'      => $this->email,
            'phone'      => $this->phone,
            'notes'      => $this->notes,
            'meta'       => $this->meta ?? [],
            'created_by' => Auth::id(),
        ];

        DB::beginTransaction();
        try {
            if ($this->client && $this->client->exists) {
                $this->client->fill($payload);
                $ok = $this->client->save(); // update → bool (رفتار Eloquent). :contentReference[oaicite:4]{index=4}
                Log::info('[Clients] update result', ['ok' => $ok, 'id' => $this->client->id]);
                $client = $this->client;
            } else {
                $client = Client::create($payload); // create → model
                Log::info('[Clients] create result', ['id' => $client?->id]);
            }

            // سنک نقش‌محور...
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
        \DB::table('clients')->where('username', $u)->exists();

        $candidate = null;

        switch ($strategy) {
            case 'email': // کل ایمیل
                $candidate = (string) $this->email;
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
                $last = \DB::table('clients')
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
        if (in_array($strategy, ['email', 'mobile'], true)) {
            // برای این دو حالت، فقط همون candidate رو برمی‌گردونیم
            // (چک یکتا در متد save انجام می‌شود و اگر تکراری بود، خطا می‌دهیم)
            \Log::info('[Clients] username candidate (strict) ', [
                'strategy'  => $strategy,
                'candidate' => $candidate,
            ]);
            return (string) $candidate;
        }

        // برای بقیه‌ی استراتژی‌ها، مثل قبل auto-increment کن
        if ($existsInClients($candidate)) {
            $candidate = $this->incrementUsernameBase($candidate, $existsInClients);
        }

        \Log::info('[Clients] username candidate (auto-unique)', [
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
