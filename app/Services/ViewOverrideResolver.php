<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

final class ViewOverrideResolver
{
    public function __construct(private ThemeManager $themeManager) {}

    /**
     * بررسی می‌کند که آیا فایل override اختصاصی برای view درخواستی در تم فعال وجود دارد یا خیر.
     *
     * ورودی: 'smartbot::page.chat' یا 'market::web.index' یا 'themes.default.index'
     * خروجی: مسری فایل جاگزین فیزیکی یا null
     */
    public function findOverridePath(string $viewName): ?string
    {
        $entity = $this->themeManager->getActiveEntity();
        if (!$entity || empty($entity->theme_key)) {
            return null;
        }

        $themeKey = strtolower($entity->theme_key);

        // ۱. اگر نام view دارای namespace ماژول است (مانند smartbot::page.chat)
        if (str_contains($viewName, '::')) {
            [$namespace, $relativeView] = explode('::', $viewName, 2);
            $relativePath = str_replace('.', '/', $relativeView);

            // مسیر فایل override در تم اختصاصی مشتری
            // resources/views/themes/{theme_key}/overrides/{namespace}/{relative_path}.blade.php
            $overrideFilePath = resource_path("views/themes/{$themeKey}/overrides/{$namespace}/{$relativePath}.blade.php");

            if (File::exists($overrideFilePath)) {
                return $overrideFilePath;
            }

            // چک اولویت دوم: اگر ماژول مبدا تعریف شده است (مثلا module_source = market)
            if (!empty($entity->module_source)) {
                $moduleSource = strtolower($entity->module_source);
                $moduleOverridePath = resource_path("views/themes/{$themeKey}/overrides/{$moduleSource}/{$relativePath}.blade.php");
                if (File::exists($moduleOverridePath)) {
                    return $moduleOverridePath;
                }
            }
        } else {
            // ۲. اگر نام view یک view عمومی است (مانند themes.default.index یا layouts.web)
            // امکان override فایل‌های عمومی نیز در پوشه overrides وجود دارد
            $relativePath = str_replace('.', '/', $viewName);
            $overrideFilePath = resource_path("views/themes/{$themeKey}/overrides/{$relativePath}.blade.php");

            if (File::exists($overrideFilePath)) {
                return $overrideFilePath;
            }
        }

        return null;
    }
}
