<?php

namespace Modules\Clients;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Modules\Clients\Entities\ClientForm;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected function trackerPath(): string
    {
        return storage_path('app/module-installer/'.$this->moduleSlug.'/created.json');
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
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

    public function __construct()
    {
        parent::__construct('Clients');
    }

    public function install(): void
    {
        parent::install();

        // guard پیش‌فرض (در صورت نیاز تغییر بده)
        $guard = config('auth.defaults.guard', 'web');

        // ----- Permissions -----
        $perms = [
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.manage',
        ];

        $tracker = $this->loadTracker();

        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name; // با name ردیابی می‌کنیم
            }
        }

//         $moduleRoles = ['clients-manager'];
         $moduleRoles = [];

        foreach ($moduleRoles as $rname) {
            $role = Role::firstOrCreate(['name' => $rname, 'guard_name' => $guard]);
            if ($role->wasRecentlyCreated) {
                $tracker['roles'][] = $role->name; // فقط نقش‌هایی که خود ماژول ساخت
            }
        }

        // به نقش‌های سراسری فقط پرمیژن بده (حذف‌شون نمی‌کنیم)
        foreach (['super-admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($perms);
        }

        // ذخیره tracker
        // یکتا کن تا تکراری‌ها انباشته نشن
        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles']       = array_values(array_unique($tracker['roles']));
        $this->saveTracker($tracker);

        // کش Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info("Clients Installer: permissions created / roles updated.");
    }


    public function uninstall(): void
    {
        // حذف پرمیژن‌ها و رول‌ها قبل از uninstall اصلی
        $this->removeModuleOwnedPermissionsAndRoles();

        // فراخوانی uninstall والد که عملیات معمول پاکسازی (migrate-rollback) را انجام می‌دهد
        parent::uninstall();

        // لاگ یا کارهای اختیاری بعد از uninstall
        Log::info("Clients Installer: uninstalled and permissions removed.");
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        // کش را خالی کن تا Spatie مراجع قدیمی نگه ندارد
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            // --- 1) اول roleهای ساخته‌شده توسط ماژول را حذف کن (اگر داری) ---
            // اما قبلش detach permissions (برای پاک‌سازی FKها/pivot)
            foreach ($tracker['roles'] ?? [] as $roleName) {
                if (! $roleName) continue;
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if ($role) {
                    $role->permissions()->detach();
                    $role->delete();
                }
            }

            // --- 2) سپس permissions ساخته‌شده توسط ماژول را حذف کن ---
            // قبل از delete، از همه roleهای باقیمانده (مثل admin/super-admin) هم detach می‌کنیم
            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (! $permName) continue;
                $perm = Permission::where('name', $permName)->where('guard_name', $guard)->first();
                if ($perm) {
                    // detach از تمام roleها
                    $perm->roles()->detach();
                    $perm->delete();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Clients Installer: removeModuleOwnedPermissionsAndRoles failed: ".$e->getMessage());
            throw $e;
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        // فایل tracker را پاک کن تا نصب بعدی از صفر شروع شود
        $path = $this->trackerPath();
        if (File::exists($path)) {
            File::delete($path);
        }
    }

}
