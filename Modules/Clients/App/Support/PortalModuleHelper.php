<?php

namespace Modules\Clients\App\Support;

use App\Models\Module as DbModule;
use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;

class PortalModuleHelper
{
    /** @var array<string, bool> */
    private static array $cache = [];

    /**
     * Determine whether a module is installed, enabled, and ready for the client portal.
     */
    public static function isActive(string $moduleName, string $tableName): bool
    {
        $cacheKey = strtolower($moduleName) . ':' . $tableName;
        if (array_key_exists($cacheKey, self::$cache)) {
            return self::$cache[$cacheKey];
        }

        if (
            !class_exists(Module::class)
            || !Module::has($moduleName)
            || !Module::isEnabled($moduleName)
            || !Schema::hasTable($tableName)
        ) {
            return self::$cache[$cacheKey] = false;
        }

        $dbModule = null;
        if (class_exists(DbModule::class) && Schema::hasTable('modules')) {
            $dbModule = DbModule::where('slug', strtolower($moduleName))
                ->orWhere('name', $moduleName)
                ->first();

            if ($dbModule && !$dbModule->active) {
                return self::$cache[$cacheKey] = false;
            }
        }

        $isInstalled = ($dbModule && (bool) $dbModule->installed)
            || (class_exists(BaseModuleInstaller::class) && BaseModuleInstaller::isInstalled($moduleName));

        return self::$cache[$cacheKey] = $isInstalled;
    }

    /**
     * Determine whether vacation rental features are enabled for the client portal.
     */
    public static function isRentalActive(): bool
    {
        return self::isActive('Properties', 'property_rental_bookings')
            && class_exists(\Modules\Properties\Entities\PropertySetting::class)
            && (bool) (
                \Modules\Properties\Entities\PropertySetting::get('rental_mode_enabled', 0)
                ?: \Modules\Properties\Entities\PropertySetting::get('rental_system_enabled', 0)
            );
    }

    /**
     * Clear the per-request cache (useful in automated tests).
     */
    public static function flushCache(): void
    {
        self::$cache = [];
    }
}
