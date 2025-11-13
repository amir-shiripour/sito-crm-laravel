<?php

namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClientForm extends Model
{
    protected $table = 'client_forms';

    protected $fillable = [
        'name',
        'key',
        'is_default',
        'schema',
    ];

    // JSON → array (برای کار راحت با فیلدها)
    protected $casts = [
        'schema' => 'array',
        'is_default' => 'boolean',
    ]; // مستند رسمی cast آرایه. :contentReference[oaicite:0]{index=0}

    /* ------------------------------------------
     | Boot: تضمین «یک فرم پیش‌فرض»
     * وقتی رکوردی با is_default=true ذخیره شود، بقیه false می‌شوند.
     ------------------------------------------ */
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
            if ($form->is_default) {
                // همهٔ رکوردهای دیگر را از پیش‌فرض خارج کن
                static::query()
                    ->where('id', '!=', $form->id)
                    ->update(['is_default' => false]); // الگوی آپدیت گروهی. :contentReference[oaicite:1]{index=1}
            }
        });
    } // درباره boot/booted. :contentReference[oaicite:2]{index=2}

    /* ------------------------------------------
     | اسکوپ‌ها و میانبرها
     ------------------------------------------ */

    // فرم پیش‌فرض (اولین true)
    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->first();
    }

    // اسکوپ: فقط فرم‌های پیش‌فرض
    public function scopeOnlyDefault(Builder $q): Builder
    {
        return $q->where('is_default', true);
    } // نمونهٔ الگوی اسکوپ. :contentReference[oaicite:3]{index=3}

    // فرم فعال با اولویت: تنظیمات DB → پیش‌فرض → آخرین
    public static function active(?string $keyFromSettings = null): ?self
    {
        if ($keyFromSettings) {
            $byKey = static::query()->where('key', $keyFromSettings)->first();
            if ($byKey) return $byKey;
        }
        return static::default() ?: static::query()->latest('id')->first();
    }

    /* ------------------------------------------
     | هلپرهای اسکیمای فرم
     ------------------------------------------ */

    // لیست فیلدهای Quick Create
    public function quickFields(): array
    {
        $fields = $this->schema['fields'] ?? [];
        return array_values(array_filter($fields, fn ($f) => !empty($f['quick_create'])));
    }

    // گرفتن فیلد با id
    public function field(string $id): ?array
    {
        foreach (($this->schema['fields'] ?? []) as $f) {
            if (($f['id'] ?? null) === $id) return $f;
        }
        return null;
    }
}
