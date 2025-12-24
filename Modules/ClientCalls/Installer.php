<?php

namespace Modules\ClientCalls;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
        parent::__construct('ClientCalls');
    }

    public function install(): void
    {
        parent::install();

        $guard = config('auth.defaults.guard', 'web');

        $perms = [
            'client-calls.view',
            'client-calls.view.all',
            'client-calls.view.assigned',
            'client-calls.view.own',
            'client-calls.create',
            'client-calls.edit',
            'client-calls.delete',
            'client-calls.manage',
        ];

        $tracker = $this->loadTracker();

        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        // نقش‌های اختصاصی خود ماژول (اگر خواستی بعداً اضافه کن)
        $moduleRoles = [];

        foreach ($moduleRoles as $rname) {
            $role = Role::firstOrCreate(['name' => $rname, 'guard_name' => $guard]);
            if ($role->wasRecentlyCreated) {
                $tracker['roles'][] = $role->name;
            }
        }

        // نقش‌های سراسری
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($perms);
        }

        // مثال برای support: فقط دیدن و ایجاد
        $support = Role::firstOrCreate(['name' => 'support', 'guard_name' => $guard]);
        $support->givePermissionTo([
            'client-calls.view',
            'client-calls.view.assigned',
            'client-calls.create',
        ]);

        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles']       = array_values(array_unique($tracker['roles']));
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info("ClientCalls Installer: permissions created / roles updated.");
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();
        parent::uninstall();
        Log::info("ClientCalls Installer: uninstalled and permissions removed.");
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard   = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            foreach ($tracker['roles'] ?? [] as $roleName) {
                if (!$roleName) continue;
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if ($role) {
                    $role->permissions()->detach();
                    $role->delete();
                }
            }

            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (!$permName) continue;
                $perm = Permission::where('name', $permName)->where('guard_name', $guard)->first();
                if ($perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("ClientCalls Installer: removeModuleOwnedPermissionsAndRoles failed: ".$e->getMessage());
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
