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
        // ثبت Singleton برای ThemeManager و ViewOverrideResolver
        $this->app->singleton(\App\Services\ThemeManager::class);
        $this->app->singleton(\App\Services\ViewOverrideResolver::class);

        // تمدید (Extend) کردن view.finder لاراول با ThemeOverrideViewFinder
        $this->app->extend('view.finder', function ($finder, $app) {
            $paths = $app['config']['view.paths'];
            $overrideFinder = new \App\View\ThemeOverrideViewFinder($app['files'], $paths);
            $overrideFinder->setResolver($app->make(\App\Services\ViewOverrideResolver::class));

            // کپی کردن تمامی هیِنت‌های ثبت شده توسط ماژول‌های nwidart (مانند smartbot::, market::)
            if (method_exists($finder, 'getHints')) {
                foreach ($finder->getHints() as $namespace => $hints) {
                    $overrideFinder->addNamespace($namespace, $hints);
                }
            }

            return $overrideFinder;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!$this->isAppInstalled() || $this->app->runningInConsole()) {
            return;
        }

        try {
            if (Schema::hasTable('themes')) {
                $activeTheme = Cache::rememberForever('active_theme', function () {
                    return Theme::where('active', true)->first();
                });

                if ($activeTheme) {
                    $themeViewPath = resource_path('views/themes/' . $activeTheme->directory_name);
                    if (is_dir($themeViewPath)) {
                        View::addLocation($themeViewPath);
                    }
                    View::share('activeTheme', $activeTheme);
                }
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * بررسی می‌کند که آیا فایل 'installed.flag' وجود دارد یا خیر.
     */
    private function isAppInstalled(): bool
    {
        try {
            return Storage::disk('local')->exists('installed.flag');
        } catch (\Exception $e) {
            return false;
        }
    }
}
