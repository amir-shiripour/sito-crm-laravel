<?php

namespace Modules\FollowUps;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected string $moduleName = 'FollowUps';

    protected function trackerPath(): string
    {
        return storage_path('app/module-install-trackers/followups.json');
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
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    public function install(): void
    {
        // اطمینان از نصب بودن ماژول Tasks (وجود جدول tasks)
        if (! Schema::hasTable('tasks')) {
            throw new \RuntimeException('جدول tasks یافت نشد. لطفاً ابتدا ماژول Tasks را نصب کنید.');
        }

        parent::install();

        $guard = config('auth.defaults.guard', 'web');

        $perms = [
            'followups.view',
            'followups.view.all',
            'followups.view.assigned',
            'followups.view.own',
            'followups.create',
            'followups.edit',
            'followups.delete',
            'followups.manage',
        ];

        $tracker = $this->loadTracker();

        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate([
                'name'       => $name,
                'guard_name' => $guard,
            ]);

            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        $moduleRoles = []; // مثال: ['followups-manager'] در صورت نیاز

        foreach ($moduleRoles as $roleName) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => $guard,
            ]);

            if ($role->wasRecentlyCreated) {
                $tracker['roles'][] = $role->name;
            }
        }

        foreach (['super-admin'] as $sysRole) {
            $role = Role::firstOrCreate([
                'name'       => $sysRole,
                'guard_name' => $guard,
            ]);

            $role->givePermissionTo($perms);
        }

        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles']       = array_values(array_unique($tracker['roles']));

        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info('FollowUps Installer: permissions created / roles updated.');
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();

        parent::uninstall();

        Log::info('FollowUps Installer: uninstalled and permissions removed.');
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard   = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();

        try {
            foreach ($tracker['roles'] ?? [] as $roleName) {
                if (! $roleName) {
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

            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (! $permName) {
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
            Log::error('FollowUps Installer: removeModuleOwnedPermissionsAndRoles failed: ' . $e->getMessage());
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
