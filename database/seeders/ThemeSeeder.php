<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Theme;
use App\Models\Module;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. تم پیش‌فرض (شرکتی/وبلاگی)
        $corporateTheme = Theme::updateOrCreate(
            ['slug' => 'corporate'],
            [
                'name' => 'تم شرکتی (پیش‌فرض)',
                'description' => 'یک تم چندمنظوره برای وب‌سایت‌های شرکتی و وبلاگ‌ها.',
                'view_path' => 'themes.corporate', // مسیر ویوها: resources/views/themes/corporate
                'active' => false,
            ]
        );

        // --- اصلاح شد: وابستگی به 'core' حذف شد ---
        // ماژول‌های مورد نیاز این تم
        $corporateModules = Module::whereIn('slug', [
            'blog' // این تم فقط به ماژول وبلاگ نیاز دارد
        ])->pluck('id');
        $corporateTheme->requiredModules()->sync($corporateModules);
        // --- پایان اصلاح ---


        // 2. تم فروشگاهی
        $shopTheme = Theme::updateOrCreate(
            ['slug' => 'shop'],
            [
                'name' => 'تم فروشگاهی',
                'description' => 'یک تم کامل برای راه‌اندازی فروشگاه آنلاین.',
                'view_path' => 'themes.shop', // مسیر ویوها: resources/views/themes/shop
                'active' => false,
            ]
        );

        // --- اصلاح شد: وابستگی به 'core' حذف شد ---
        // ماژول‌های مورد نیاز این تم
        $shopModules = Module::whereIn('slug', [
            'shop' // این تم به ماژول فروشگاه نیاز دارد
        ])->pluck('id');
        $shopTheme->requiredModules()->sync($shopModules);
        // --- پایان اصلاح ---
    }
}

