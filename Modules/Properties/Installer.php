<?php

namespace Modules\Properties;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
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
        parent::__construct('Properties');
    }

    public function install(): void
    {
        parent::install();
        $this->createPermissions();
    }

    public function createPermissions(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        // لیست کامل پرمیژن‌های ماژول املاک
        $perms = [
            // Properties (Main)
            'properties.view',
            'properties.view.all',
            'properties.view.own',
            'properties.create',
            'properties.edit',
            'properties.edit.all',
            'properties.edit.own',
            'properties.delete',
            'properties.delete.all',
            'properties.delete.own',
            'properties.manage',

            // Settings
            'properties.settings.manage',

            // Categories
            'properties.categories.view',
            'properties.categories.create',
            'properties.categories.edit',
            'properties.categories.delete',
            'properties.categories.manage',

            // Attributes
            'properties.attributes.view',
            'properties.attributes.create',
            'properties.attributes.edit',
            'properties.attributes.delete',
            'properties.attributes.manage',

            // Owners
            'properties.owners.view',
            'properties.owners.create',
            'properties.owners.edit',
            'properties.owners.delete',
            'properties.owners.manage',
        ];

        $tracker = $this->loadTracker();

        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );

            // اگر تازه ساخته شده یا در ترکر نیست، اضافه کن
            if ($perm->wasRecentlyCreated || !in_array($perm->name, $tracker['permissions'] ?? [])) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        // اگر ماژول نقش‌های اختصاصی دارد اینجا اضافه کنید
        $moduleRoles = [];

        foreach ($moduleRoles as $rname) {
            $role = Role::firstOrCreate(['name' => $rname, 'guard_name' => $guard]);
            if ($role->wasRecentlyCreated || !in_array($role->name, $tracker['roles'] ?? [])) {
                $tracker['roles'][] = $role->name;
            }
        }

        // اختصاص پرمیژن‌ها به super-admin
        foreach (['super-admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($perms);
        }

        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles']       = array_values(array_unique($tracker['roles']));
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info("Properties Installer: permissions created / roles updated.");
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();
        parent::uninstall();
        Log::info("Properties Installer: uninstalled and permissions removed.");
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            // 1. حذف نقش‌های ساخته شده توسط ماژول
            foreach ($tracker['roles'] ?? [] as $roleName) {
                if (! $roleName) continue;
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if ($role) {
                    $role->permissions()->detach();
                    $role->delete();
                }
            }

            // 2. حذف پرمیژن‌های ساخته شده توسط ماژول
            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (! $permName) continue;
                $perm = Permission::where('name', $permName)->where('guard_name', $guard)->first();
                if ($perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Properties Installer: removeModuleOwnedPermissionsAndRoles failed: ".$e->getMessage());
            throw $e;
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $path = $this->trackerPath();
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
