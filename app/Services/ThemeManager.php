<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Modules\Settings\Entities\Setting;

final class ThemeManager
{
    private ?object $activeEntity = null;
    private bool $entityChecked = false;

    /**
     * بررسی می‌کند آیا سیستم نصب شده است یا خیر.
     */
    private function isInstalled(): bool
    {
        return File::exists(storage_path('app/installed.flag'));
    }

    /**
     * بررسی فعال بودن ماژول ContentForge
     */
    private function isContentForgeActive(): bool
    {
        if (!class_exists(\Nwidart\Modules\Facades\Module::class)) {
            return false;
        }

        try {
            return \Nwidart\Modules\Facades\Module::has('ContentForge')
                && \Nwidart\Modules\Facades\Module::isEnabled('ContentForge');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * موجودیت فعال انتخاب شده در تنظیمات را برمی‌گرداند.
     */
    public function getActiveEntity(): ?object
    {
        if ($this->entityChecked) {
            return $this->activeEntity;
        }

        $this->entityChecked = true;

        // اگر سیستم هنوز نصب نشده باشد یا ماژول ContentForge فعال نباشد، دسترسی به دیتابیس انجام نمی‌شود
        if (!$this->isInstalled() || !$this->isContentForgeActive()) {
            return null;
        }

        try {
            if (class_exists(\Modules\ContentForge\App\Models\ContentEntity::class) && \Illuminate\Support\Facades\Schema::hasTable('content_entities')) {
                $entityId = Setting::where('key', 'content_entity_id')->value('value');
                if ($entityId) {
                    // کش کردن اطلاعات به مدت ۶۰ ثانیه برای بهینه‌سازی
                    $this->activeEntity = Cache::remember("active_theme_entity_{$entityId}", 60, function () use ($entityId) {
                        return \Modules\ContentForge\App\Models\ContentEntity::where('id', $entityId)
                            ->where('is_active', true)
                            ->first();
                    });
                }
            }
        } catch (\Throwable $e) {
            $this->activeEntity = null;
        }

        return $this->activeEntity;
    }

    /**
     * زنجیره اولویت قالب‌ها را برمی‌گرداند.
     */
    public function getThemeChain(): array
    {
        $chain = [];
        $entity = $this->getActiveEntity();

        if ($entity) {
            if (!empty($entity->theme_key)) {
                $chain[] = strtolower($entity->theme_key);
            }
            if (!empty($entity->module_source)) {
                $chain[] = strtolower($entity->module_source);
            }
        }

        // افزودن قالب پیش‌فرض ثبت شده در تنظیمات سیستم
        $appTheme = 'default';

        if ($this->isInstalled()) {
            try {
                $appTheme = Setting::where('key', 'app_theme')->value('value') ?? 'default';
            } catch (\Throwable $e) {
                $appTheme = 'default';
            }
        }

        $chain[] = strtolower($appTheme);

        if (strtolower($appTheme) !== 'default') {
            $chain[] = 'default';
        }

        return array_values(array_unique(array_filter($chain)));
    }

    /**
     * یافتن و رزرو کردن یک فایل View در زنجیره اولویت.
     */
    public function resolveView(string $viewName, ?string $fallback = null): string
    {
        $chain = $this->getThemeChain();

        foreach ($chain as $theme) {
            $viewPath = "themes.{$theme}.{$viewName}";
            if (view()->exists($viewPath)) {
                return $viewPath;
            }
        }

        if ($fallback && view()->exists($fallback)) {
            return $fallback;
        }

        return "themes.default.{$viewName}";
    }

    /**
     * بازگرداندن کل زنجیره قالب‌ها به صورت آرایه جهت استفاده در includeFirst
     */
    public function resolveViewChain(string $viewName): array
    {
        $chain = $this->getThemeChain();
        $views = [];

        foreach ($chain as $theme) {
            $views[] = "themes.{$theme}.{$viewName}";
        }

        $views[] = "themes.default.{$viewName}";

        return array_values(array_unique($views));
    }

    /**
     * بررسی می‌کند آیا قالب فعال یک پروژه SPA است یا خیر.
     */
    public function isSpaTheme(): bool
    {
        $entity = $this->getActiveEntity();
        if (!$entity || empty($entity->theme_key)) {
            return false;
        }

        $themePath = resource_path("views/themes/{$entity->theme_key}/theme.json");
        if (File::exists($themePath)) {
            try {
                $config = json_decode(File::get($themePath), true);
                return isset($config['type']) && strtolower($config['type']) === 'spa';
            } catch (\Throwable $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * مدیریت خروجی SPA برای رند یا ریدایرکت.
     */
    public function handleSpaResponse()
    {
        $entity = $this->getActiveEntity();
        if (!$entity || empty($entity->theme_key)) {
            abort(404, 'Theme not configured.');
        }

        $themePath = resource_path("views/themes/{$entity->theme_key}/theme.json");
        $config = json_decode(File::get($themePath), true);

        // اگر در محیط توسعه هستیم و پورت توسعه مشخص شده، ریدایرکت ساده انجام بده
        if (config('app.env') === 'local' && isset($config['dev_port'])) {
            return redirect()->away("http://localhost:{$config['dev_port']}");
        }

        // در محیط پروداکشن، فایل بیلد استاتیک را سرو کن
        $entryFile = $config['entry_file'] ?? 'index.html';
        $buildPath = public_path("themes/{$entity->theme_key}/{$entryFile}");

        if (File::exists($buildPath)) {
            return response()->file($buildPath);
        }

        // در صورت عدم بیلد، فایل داخلی را چک کن
        $internalPath = resource_path("views/themes/{$entity->theme_key}/dist/{$entryFile}");
        if (File::exists($internalPath)) {
            return response()->file($internalPath);
        }

        abort(500, "SPA build file not found for theme: {$entity->theme_key}");
    }
}
