<?php

namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ClientForm extends Model
{
    protected $table = 'client_forms';

    protected $fillable = ['name','key','is_active','schema'];

    protected $casts = [
        'schema'    => 'array',
        'is_active' => 'bool',
    ];

    /**
     * فیلدهای سیستمی رزرو‌شده
     * (از این کلیدها نمی‌تونیم برای فیلد سفارشی استفاده کنیم)
     */
    public const SYSTEM_FIELDS = [
        'username'      => ['label' => 'نام کاربری',        'column' => 'username'],
        'full_name'     => ['label' => 'نام و نام خانوادگی', 'column' => 'full_name'],
        'email'         => ['label' => 'ایمیل',              'column' => 'email'],
        'phone'         => ['label' => 'شماره تماس',         'column' => 'phone'],
        'national_code' => ['label' => 'کد ملی',             'column' => 'national_code'],
        'status_id'     => ['label' => 'وضعیت',              'column' => 'status_id'],
        'notes'         => ['label' => 'یادداشت مدیریتی',    'column' => 'notes'],
        'password'      => ['label' => 'رمز عبور',           'column' => 'password'],
    ];

    public static function default(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * تعریف کامل فیلدهای سیستمی برای فرم‌ساز
     */
    public static function systemFieldDefaults(): array
    {
        return [
            'full_name' => [
                'id'           => 'full_name',
                'type'         => 'text',
                'label'        => 'نام و نام خانوادگی',
                'placeholder'  => 'مثلاً: علی محمدی',
                'group'        => 'اطلاعات هویتی',
                'width'        => '1/2',
                'required'     => true,
                'quick_create' => true,
                'is_system'    => true,
                'required_status_keys' => [], // الزامی براساس وضعیت (درصورت نیاز)
            ],
            'phone' => [
                'id'           => 'phone',
                'type'         => 'text',
                'label'        => 'شماره تماس',
                'placeholder'  => '0912...',
                'group'        => 'اطلاعات هویتی',
                'width'        => '1/2',
                'required'     => true,
                'quick_create' => true,
                'is_system'    => true,
                'required_status_keys' => [],
            ],
            'email' => [
                'id'           => 'email',
                'type'         => 'email',
                'label'        => 'ایمیل',
                'placeholder'  => 'example@domain.com',
                'group'        => 'اطلاعات هویتی',
                'width'        => '1/2',
                'required'     => false,
                'quick_create' => true,
                'is_system'    => true,
                'required_status_keys' => [],
            ],
            'national_code' => [
                'id'           => 'national_code',
                'type'         => 'text',
                'label'        => 'کد ملی',
                'placeholder'  => 'مثلاً: 0012345678',
                'group'        => 'اطلاعات هویتی',
                'width'        => '1/2',
                'required'     => false,
                'quick_create' => false,
                'is_system'    => true,
                'required_status_keys' => [],
            ],
            'status_id' => [
                'id'           => 'status_id',
                'type'         => 'status',   // مهم برای رندر فیلد وضعیت
                'label'        => 'وضعیت پرونده',
                'required'     => true,
                'quick_create' => true,
                'width'        => 'full',
                'group'        => 'وضعیت',
                'is_system'    => true,
                'required_status_keys' => [], // معمولاً خالی می‌مونه
            ],
            'password' => [
                'id'           => 'password',
                'type'         => 'password',
                'label'        => 'رمز عبور ورود به پنل',
                'placeholder'  => 'حداقل ۸ کاراکتر امن',
                'group'        => 'اطلاعات ورود',
                'width'        => '1/2',
                'required'     => true,      // ← الزامی (در فرم‌ساز هم قابل تغییر است)
                'quick_create' => false,     // در ایجاد سریع نمی‌خواهیم
                'is_system'    => true,
                'required_status_keys' => [], // اگر بعداً خواستی شرطی براساس وضعیت بزاری
            ],
            'notes' => [
                'id'           => 'notes',
                'type'         => 'textarea',
                'label'        => 'یادداشت مدیریتی',
                'placeholder'  => 'توضیحات اضافی در مورد این کاربر...',
                'group'        => 'یادداشت‌ها',
                'width'        => 'full',
                'required'     => false,
                'quick_create' => true,
                'is_system'    => true,
                'required_status_keys' => [],
            ],
        ];
    }

    /**
     * شناسه‌های رزرو‌شده‌ی فیلدهای سیستمی
     */
    public static function reservedFieldIds(): array
    {
        return array_keys(static::SYSTEM_FIELDS);
    }

    public static function isSystemFieldId(string $id): bool
    {
        return in_array($id, static::reservedFieldIds(), true);
    }

    /**
     * نرمال‌سازی اسکیمای فرم قبل از ذخیره
     *  - اطمینان از داشتن id
     *  - تثبیت فیلدهای سیستمی طبق systemFieldDefaults
     *  - نرمال‌سازی required_status_keys به آرایه
     */
    public static function normalizeSchema(array $schema): array
    {
        $fields         = $schema['fields'] ?? [];
        $systemDefaults = static::systemFieldDefaults();

        $normalized = [];

        foreach ($fields as $f) {
            if (!is_array($f)) {
                continue;
            }

            // id اجباری
            if (empty($f['id'])) {
                $base  = ($f['type'] ?? 'fld') . '_' . substr((string) str()->uuid(), 0, 8);
                $f['id'] = $base;
            }

            $fid = $f['id'];

            // اگر فیلد سیستمی است → روی تعریف سیستمی قفل کن (ولی overrideهای کاربر رو نگه می‌داریم)
            if (isset($systemDefaults[$fid])) {
                $canon = $systemDefaults[$fid];

                $f['id']        = $canon['id'];
                $f['type']      = $canon['type'];
                $f['is_system'] = true;

                if (!isset($f['label']) || $f['label'] === '') {
                    $f['label'] = $canon['label'];
                }

                foreach (['group','width','placeholder','quick_create','required'] as $k) {
                    if (!array_key_exists($k, $f) && array_key_exists($k, $canon)) {
                        $f[$k] = $canon[$k];
                    }
                }

                // required_status_keys برای فیلدهای سیستمی هم ساپورت می‌کنیم
                if (!array_key_exists('required_status_keys', $f) && array_key_exists('required_status_keys', $canon)) {
                    $f['required_status_keys'] = $canon['required_status_keys'];
                }
            }

            // نرمال‌سازی required_status_keys برای همهٔ فیلدها (سیستمی + سفارشی)
            if (!empty($f['required_status_keys'])) {
                // اگر string بود (مثلاً "canceled,blacklist") → به آرایه تبدیل کن
                if (is_string($f['required_status_keys'])) {
                    $parts = array_map('trim', explode(',', $f['required_status_keys']));
                    $f['required_status_keys'] = array_values(array_filter($parts));
                } elseif (is_array($f['required_status_keys'])) {
                    $f['required_status_keys'] = array_values(array_filter(array_map('trim', $f['required_status_keys'])));
                }
            } else {
                $f['required_status_keys'] = [];
            }

            $normalized[] = $f;
        }

        $schema['fields'] = array_values($normalized);
        return $schema;
    }

    protected static function booted(): void
    {
        static::saving(function (self $form) {
            // key یکتا و تمیز
            $form->key = static::generateUniqueKey(
                $form->key ?: $form->name,
                $form->id
            );

            // حداقل اسکیمای سالم
            $schema = $form->schema ?? [];
            if (!is_array($schema)) {
                $schema = [];
            }

            if (empty($schema['fields']) || !is_array($schema['fields'])) {
                $schema['fields'] = [];
            }

            // نرمال‌سازی روی فیلدها
            $schema = static::normalizeSchema($schema);

            $form->schema = $schema;
        });

        static::saved(function (self $form) {
            if ($form->is_active) {
                static::query()
                    ->where('id', '!=', $form->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    public function scopeOnlyDefault(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public static function active(?string $preferredKey = null): ?self
    {
        if ($preferredKey) {
            $f = static::where('key', $preferredKey)
                ->where('is_active', true)
                ->first();

            if ($f) {
                return $f;
            }
        }

        return static::where('is_active', true)->first() ?: static::first();
    }

    public static function generateUniqueKey(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base, '_') ?: 'form';
        $key  = $base;
        $i    = 1;

        while (static::where('key', $key)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $key = $base.'_'.$i;
            $i++;
        }

        return $key;
    }

    public function quickFields(): array
    {
        $fields = $this->schema['fields'] ?? [];

        return array_values(array_filter($fields, function ($f) {
            return !empty($f['quick_create']);
        }));
    }

    public function field(string $id): ?array
    {
        foreach (($this->schema['fields'] ?? []) as $f) {
            if (($f['id'] ?? null) === $id) {
                return $f;
            }
        }
        return null;
    }
}
