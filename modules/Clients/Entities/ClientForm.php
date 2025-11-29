<?php

namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
class ClientForm extends Model
{
    protected $table = 'client_forms';

    protected $fillable = ['name','key','is_active','schema'];
    protected $casts = ['schema' => 'array', 'is_active' => 'bool'];
    public const SYSTEM_FIELDS = [
        'username'      => ['label' => 'نام کاربری',        'column' => 'username'],
        'full_name'     => ['label' => 'نام و نام خانوادگی', 'column' => 'full_name'],
        'email'         => ['label' => 'ایمیل',          'column' => 'email'],
        'phone'         => ['label' => 'شماره تماس',     'column' => 'phone'],
        'national_code' => ['label' => 'کد ملی',         'column' => 'national_code'],
        'status_id' => ['label' => 'وضعیت',         'column' => 'status_id'],
    ];
    public static function default(): ?self
    {
        return static::where('is_active', true)->first();
    }

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
            ],
            'status_id'     => [
                'id'           => 'status_id',
                'type'         => 'status',
                'label'        => 'وضعیت پرونده',
                'required'     => false,
                'quick_create' => true,
                'width'        => 'full',
                'group'        => 'وضعیت',
                'is_system'    => true,
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
            ],
        ];
    }


    protected static function booted(): void
    {
        static::saving(function (self $form) {
            // اطمینان از key تمیز (slug سبک) — اگر لازم نیست، حذف کن
            $form->key = str($form->key ?: $form->name)->slug('_');

            // حداقل اسکیمای سالم
            $schema = $form->schema ?? [];
            if (!is_array($schema)) $schema = [];
            $schema['fields'] = array_values(array_map(function ($f) {
                // آیدی خودکار اگر خالی بود
                if (empty($f['id'])) {
                    $base = ($f['type'] ?? 'fld') . '_' . substr((string) str()->uuid(), 0, 8);
                    $f['id'] = $base;
                }
                return $f;
            }, ($schema['fields'] ?? [])));
            $form->schema = $schema;
        });

        static::saved(function (self $form) {
            if ($form->is_active) {
                // همهٔ رکوردهای دیگر را از پیش‌فرض خارج کن
                static::query()
                    ->where('id', '!=', $form->id)
                    ->update(['is_active' => false]); // الگوی آپدیت گروهی. :contentReference[oaicite:1]{index=1}
            }
        });
    } // درباره boot/booted. :contentReference[oaicite:2]{index=2}

    public function scopeOnlyDefault(Builder $q): Builder
    {
        return $q->where('is_active', true);
    } // نمونهٔ الگوی اسکوپ. :contentReference[oaicite:3]{index=3}

    public static function active(?string $preferredKey = null): ?self
    {
        if ($preferredKey) {
            $f = static::where('key', $preferredKey)->where('is_active', true)->first();
            if ($f) return $f;
        }

        return static::where('is_active', true)->first() ?: static::first();
    }

    public static function generateUniqueKey(string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'form';
        $key  = $base;
        $i    = 1;

        while (static::where('key', $key)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $key = $base.'-'.$i;
            $i++;
        }

        return $key;
    }

    public function quickFields(): array
    {
        $fields = $this->schema['fields'] ?? [];
        return array_values(array_filter($fields, fn ($f) => !empty($f['quick_create'])));
    }

    public function field(string $id): ?array
    {
        foreach (($this->schema['fields'] ?? []) as $f) {
            if (($f['id'] ?? null) === $id) return $f;
        }
        return null;
    }
}
