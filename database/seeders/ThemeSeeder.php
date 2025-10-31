<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Theme;
use App\Models\Module;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;


class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Theme: Modern Corporate (Default)
        // این تم فقط به ماژول‌های هسته نیاز دارد
        $corporate = Theme::updateOrCreate(['slug' => 'modern-corporate'], [
            'name' => 'تم شرکتی مدرن',
            'description' => 'یک تم پیش‌فرض و تمیز برای سایت‌های شرکتی.',
            'version' => '1.0.0',
            'view_path' => 'themes.modern-corporate', // مسیر ویوها: resources/views/themes/modern-corporate
            'active' => true, // تم پیش‌فرض فعال
        ]);

        // 2. Theme: E-commerce Shop
        // این تم به ماژول‌های هسته + ماژول "فروشگاه" (shop) نیاز دارد
        $shop = Theme::updateOrCreate(['slug' => 'e-commerce-shop'], [
            'name' => 'تم فروشگاهی',
            'description' => 'یک تم برای راه‌اندازی فروشگاه آنلاین.',
            'version' => '1.0.0',
            'view_path' => 'themes.e-commerce-shop',
            'active' => false,
        ]);

        // 3. Theme: Blog / News
        // این تم به ماژول‌های هسته + ماژول "وبلاگ" (blog) نیاز دارد
        $blog = Theme::updateOrCreate(['slug' => 'blog-news'], [
            'name' => 'تم وبلاگ و خبری',
            'description' => 'یک تم برای سایت‌های محتوامحور و خبری.',
            'version' => '1.0.0',
            'view_path' => 'themes.blog-news',
            'active' => false,
        ]);


        // --- Sincronize module requirements ---
        // این بخش روابط تم‌ها و ماژول‌ها را در جدول واسط ثبت می‌کند

        // بررسی می‌کنیم که جدول ماژول‌ها وجود داشته باشد
        if (!Schema::hasTable('modules')) {
            Log::error('ThemeSeeder: Missing modules table. Run ModuleSeeder first.');
            $this->command->error('جدول ماژول‌ها یافت نشد. لطفاً ابتدا ModuleSeeder را اجرا کنید.');
            return;
        }

        // ماژول‌های هسته که همه تم‌ها به آن‌ها نیاز دارند
        $coreModules = Module::whereIn('slug', ['core', 'users', 'profile', 'modules', 'themes'])
            ->pluck('id');

        // اتصال ماژول‌های هسته به تم شرکتی
        $corporate->requiredModules()->sync($coreModules);

        // ---
        // اتصال ماژول‌های هسته + ماژول فروشگاه به تم فروشگاهی
        $shopModule = Module::where('slug', 'shop')->first();
        if ($shopModule) {
            $shopModules = $coreModules->push($shopModule->id);
            $shop->requiredModules()->sync($shopModules);
        } else {
            $this->command->warn('ماژول "shop" در ThemeSeeder یافت نشد.');
        }

        // ---
        // اتصال ماژول‌های هسته + ماژول وبلاگ به تم وبلاگ
        $blogModule = Module::where('slug', 'blog')->first();
        if ($blogModule) {
            $blogModules = $coreModules->push($blogModule->id); // Note: $coreModules might be modified, re-fetch if needed
            // Re-fetch core modules to avoid mutation issues
            $coreModulesForBlog = Module::whereIn('slug', ['core', 'users', 'profile', 'modules', 'themes'])->pluck('id');
            $blogModules = $coreModulesForBlog->push($blogModule->id);
            $blog->requiredModules()->sync($blogModules);
        } else {
            $this->command->warn('ماژول "blog" در ThemeSeeder یافت نشد.');
        }
    }
}

