<?php

namespace Modules\Sms;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    /**
     * مسیر فایل tracker برای ذخیره لیست permission/role هایی
     * که خود ماژول ساخته تا هنگام uninstall فقط همان‌ها پاک شوند.
     */
    protected function trackerPath(): string
    {
        return storage_path('app/module-installer/' . $this->moduleSlug . '/created.json');
    }

    protected function loadTracker(): array
    {
        $path = $this->trackerPath();

        if (File::exists($path)) {
            return json_decode(File::get($path), true) ?: [];
        }

        return ['permissions' => [], 'roles' => []];
    }

    protected function saveTracker(array $data): void
    {
        $path = $this->trackerPath();
        File::ensureDirectoryExists(dirname($path));
        File::put(
            $path,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    public function __construct()
    {
        // نام ماژول (مطابق با module.json)
        parent::__construct('Sms');
    }

    /**
     * نصب ماژول SMS
     */
    public function install(): void
    {
        // اجرای migrate و کارهای پایه از BaseModuleInstaller
        parent::install();

        $guard = config('auth.defaults.guard', 'web');

        // ----- Permissions -----
        $perms = [
            'sms.view',             // مشاهده لیست پیامک‌ها و لاگ‌ها
            'sms.send',             // ارسال دستی پیامک
            'sms.templates.manage', // مدیریت الگوها / pattern‌ها
            'sms.settings.view',    // مشاهده تنظیمات SMS
            'sms.settings.manage',  // ویرایش تنظیمات (api key، خط ارسال و...)
            'sms.reports.view',     // مشاهده گزارش‌ها / آمار
            'sms.manage',           // دسترسی مدیریتی کلی
            'sms.messages.view',
            'sms.messages.send',
        ];

        $tracker = $this->loadTracker();

        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );

            if ($perm->wasRecentlyCreated) {
                // با name پرمیژن‌ها را ردیابی می‌کنیم
                $tracker['permissions'][] = $perm->name;
            }
        }

        // اگر خواستی نقش اختصاصی ماژول بسازی، اینجا اضافه کن:
        // $moduleRoles = ['sms-manager'];
        $moduleRoles = [];

        foreach ($moduleRoles as $rname) {
            $role = Role::firstOrCreate(['name' => $rname, 'guard_name' => $guard]);
            if ($role->wasRecentlyCreated) {
                $tracker['roles'][] = $role->name;
            }

            // اگر خواستی، برای role اختصاصی هم پرمیژن‌ها را ست کن
            $role->givePermissionTo($perms);
        }

        // نقش‌های سراسری (پاک نمی‌شوند، فقط permission می‌گیرند)
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($perms);
        }

        // ذخیره tracker به صورت یکتا
        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles'] = array_values(array_unique($tracker['roles']));

        $this->saveTracker($tracker);

        // خالی کردن کش Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info("Sms Installer: permissions created / roles updated.");
    }

    /**
     * Uninstall ماژول SMS
     */
    public function uninstall(): void
    {
        // ابتدا پاک‌سازی permission/role هایی که خود ماژول ساخته
        $this->removeModuleOwnedPermissionsAndRoles();

        // rollback مایگریشن‌ها و کارهای پایه
        parent::uninstall();

        Log::info("Sms Installer: uninstalled and permissions removed.");
    }

    /**
     * حذف permission/role هایی که در این ماژول ساخته شده‌اند
     */
    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();

        try {
            // --- 1) حذف role های ساخته‌شده توسط ماژول ---
            foreach ($tracker['roles'] ?? [] as $roleName) {
                if (!$roleName) {
                    continue;
                }

                $role = Role::where('name', $roleName)
                    ->where('guard_name', $guard)
                    ->first();

                if ($role) {
                    $role->permissions()->detach();
                    $role->delete();
                }
            }

            // --- 2) حذف permission های ساخته‌شده توسط ماژول ---
            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (!$permName) {
                    continue;
                }

                $perm = Permission::where('name', $permName)
                    ->where('guard_name', $guard)
                    ->first();

                if ($perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Sms Installer: removeModuleOwnedPermissionsAndRoles failed: " . $e->getMessage());
            throw $e;
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        // حذف فایل tracker تا نصب بعدی از صفر شروع شود
        $path = $this->trackerPath();
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
