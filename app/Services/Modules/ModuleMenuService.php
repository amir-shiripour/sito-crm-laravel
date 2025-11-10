<?php

namespace App\Services\Modules;

use App\Models\Module;
use Illuminate\Support\Str;

class ModuleMenuService
{
    /**
     * بازگرداندن آیتم‌های منو برای یک کاربر مشخص (فقط از ماژول‌های installed+active)
     * هر ماژول می‌تواند فایل Resources/menu.php داشته باشد که آرایه‌ای از آیتم‌ها برمی‌گرداند.
     */
    public function getAllForUser($user): array
    {
        $items = [];

        $modules = Module::where('installed', true)->where('active', true)->get();

        foreach ($modules as $module) {
            // مسیر فایل menu در ماژول
            $moduleName = Str::studly($module->slug);
            $menuPath = base_path("modules/{$moduleName}/Resources/menu.php");

            if (file_exists($menuPath)) {
                try {
                    $menu = include $menuPath;
                    if (is_array($menu)) {
                        foreach ($menu as $m) {
                            // اگر permission تعریف شده باشد و کاربر دسترسی ندارد، رد کن
                            if (!empty($m['permission'])) {
                                if (! $user->can($m['permission'])) {
                                    continue;
                                }
                            }
                            $items[] = $m;
                        }
                    }
                } catch (\Throwable $e) {
                    // لاگ کن و به ماژول بعدی برو
                    \Log::warning("Failed to load menu for module {$moduleName}: " . $e->getMessage());
                    continue;
                }
            }
        }

        // می‌توانید اینجا sort یا group انجام دهید
        return $items;
    }
}
