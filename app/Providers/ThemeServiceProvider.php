<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Theme;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * این متد، منطق اصلی بارگذاری تم است.
     */
    public function boot(): void
    {
        // ۱. بررسی می‌کنیم که آیا اصلاً برنامه نصب شده است یا نه.
        // اگر نصب نشده باشد (مثلاً در حال اجرای نصب‌کننده هستیم) یا
        // اگر در حال اجرای دستورات ترمینال (cli) هستیم، هیچ کاری نکن.
        if (!$this->isAppInstalled() || $this->app->runningInConsole()) {
            return;
        }

        try {
            // ۲. تم فعال را از دیتابیس پیدا می‌کنیم (و برای سرعت بیشتر، آن را کش می‌کنیم)
            $activeTheme = Cache::rememberForever('active_theme', function () {
                return Theme::where('active', true)->first();
            });

            if ($activeTheme) {
                // ۳. مسیر ویوهای تم فعال را به لاراول اضافه می‌کنیم
                // مثال: resources/views/themes/corporate
                $themeViewPath = resource_path('views/themes/' . $activeTheme->directory_name);

                if (is_dir($themeViewPath)) {
                    // به لاراول می‌گوییم که *اول* در این مسیر به دنبال ویو بگردد
                    View::addLocation($themeViewPath);
                }

                // ۴. متغیر $activeTheme را با تمام ویوها به اشتراک می‌گذاریم
                // تا در فایل‌های blade بتوانیم به اطلاعات تم (مثل نام) دسترسی داشته باشیم
                View::share('activeTheme', $activeTheme);
            }
        } catch (\Exception $e) {
            // اگر در اتصال به دیتابیس (مثلاً جدول themes) خطایی رخ داد،
            // برنامه نباید کرش کند، فقط از تم پیش‌فرض استفاده می‌کند.
            // (این خطا در عمل نباید رخ دهد مگر اینکه دیتابیس پاک شده باشد)
            report($e);
        }
    }

    /**
     * بررسی می‌کند که آیا فایل 'installed.flag' وجود دارد یا خیر.
     */
    private function isAppInstalled(): bool
    {
        // ما این را هم کش می‌کنیم تا در هر درخواست، فایل سیستم خوانده نشود
        return Cache::rememberForever('app_installed_status', function () {
            return Storage::disk('local')->exists('installed.flag');
        });
    }
}
