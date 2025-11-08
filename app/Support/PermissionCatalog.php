<?php
// app/Support/PermissionCatalog.php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionCatalog
{
    /**
     * تعریف گروه‌ها و الگوهای تشخیص (بر اساس پیشوند/وایلدکارد ساده)
     * key = شناسه گروه، title = تیتر فارسی، matchers = الگوهای شروع (prefix)
     */
    public static function groups(): array
    {
        return [
            'users' => [
                'title'    => 'کاربران',
                'matchers' => ['users.','menu.see.users'],
            ],
            'roles' => [
                'title'    => 'نقش‌ها و دسترسی‌ها',
                'matchers' => ['roles.','menu.see.roles','roles.assign-permissions'],
            ],
            'custom_fields' => [
                'title'    => 'فیلدهای سفارشی',
                'matchers' => ['custom-fields.','menu.see.custom-fields'],
            ],
            'menus' => [
                'title'    => 'منوها',
                'matchers' => ['menu.'],
            ],
            'other' => [
                'title'    => 'سایر',
                'matchers' => [''], // fallback
            ],
        ];
    }

    /**
     * ترجمه فارسی هر permission key
     */
    public static function translate(string $name): string
    {
        static $map = [
            // Users
            'users.view'           => 'مشاهده کاربران',
            'users.create'         => 'ایجاد کاربر',
            'users.update'         => 'ویرایش کاربر',
            'users.delete'         => 'حذف کاربر',
            'users.assign-roles'   => 'تخصیص نقش به کاربر',

            // Roles
            'roles.view'               => 'مشاهده نقش‌ها',
            'roles.create'             => 'ایجاد نقش',
            'roles.update'             => 'ویرایش نقش',
            'roles.delete'             => 'حذف نقش',
            'roles.assign-permissions' => 'تخصیص مجوز به نقش',

            // Menus
            'menu.see.users'           => 'نمایش منوی کاربران',
            'menu.see.roles'           => 'نمایش منوی نقش‌ها',

            // Custom Fields
            'menu.see.custom-fields'   => 'نمایش منوی فیلدهای سفارشی',
            'custom-fields.view'       => 'مشاهده فیلدها',
            'custom-fields.create'     => 'ایجاد فیلد',
            'custom-fields.update'     => 'ویرایش فیلد',
            'custom-fields.delete'     => 'حذف فیلد',
        ];

        // اگر ترجمه صریح نداریم، یک تبدیل خوانا بساز:
        if (!isset($map[$name])) {
            // users.export => "users export"
            $tmp = str_replace(['.', '-','_'], ' ', $name);
            // حرف اول هر کلمه بزرگ نمی‌کنیم (فارسی)، فقط فاصله‌ها را نگه می‌داریم
            return trim($tmp);
        }
        return $map[$name];
    }

    /**
     * تشخیص گروه برای یک نام مجوز
     */
    public static function detectGroup(string $name): string
    {
        foreach (self::groups() as $key => $g) {
            foreach ($g['matchers'] as $prefix) {
                if ($prefix === '' || str_starts_with($name, $prefix)) {
                    return $key;
                }
            }
        }
        return 'other';
    }

    /**
     * خروجی نهایی برای ویو: [
     *   groupKey => ['title' => ..., 'items' => [ ['name'=>'users.view','label'=>'مشاهده کاربران'], ... ] ]
     * ]
     */
    public static function groupAndTranslate(Collection $permissions): array
    {
        $out = [];
        foreach (self::groups() as $k => $g) {
            $out[$k] = ['title' => $g['title'], 'items' => []];
        }

        foreach ($permissions as $perm) {
            /** @var Permission $perm */
            $name  = $perm->name;
            $label = self::translate($name);
            $group = self::detectGroup($name);
            $out[$group]['items'][] = ['name' => $name, 'label' => $label];
        }

        // گروه‌هایی که خالی مانده‌اند را حذف کن
        foreach ($out as $k => $g) {
            if (empty($g['items'])) unset($out[$k]);
        }

        // مرتب‌سازی داخلی هر گروه بر اساس برچسب فارسی
        foreach ($out as &$g) {
            usort($g['items'], fn($a,$b) => strcmp($a['label'], $b['label']));
        }

        return $out;
    }
}
