<?php

namespace Modules\Booking;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected string $moduleName = 'Booking';

    protected function trackerPath(): string
    {
        return storage_path('app/module-install-trackers/booking.json');
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
        parent::install();

        $guard = config('auth.defaults.guard', 'web');

        // The single source of truth for this module's permissions.
        $definedPermissions = [
            'booking.view',
            'booking.manage',

            'booking.settings.manage',

            'booking.categories.view',
            'booking.categories.create',
            'booking.categories.edit',
            'booking.categories.delete',
            'booking.categories.manage',

            'booking.forms.view',
            'booking.forms.create',
            'booking.forms.edit',
            'booking.forms.delete',
            'booking.forms.manage',

            'booking.services.view',
            'booking.services.create',
            'booking.services.edit',
            'booking.services.delete',
            'booking.services.manage',

            'booking.availability.manage',

            'booking.appointments.view',
            'booking.appointments.view.all',
            'booking.appointments.view.own',
            'booking.appointments.create',
            'booking.appointments.edit',
            'booking.appointments.cancel',
            'booking.appointments.manage',

            'booking.reports.view',

            // Statement permissions
            'booking.statement.view',
            'booking.statement.view.all',
            'booking.statement.view.own',
            'booking.statement.create',
            'booking.statement.edit',
            'booking.statement.delete',
            'booking.statement.manage',
        ];

        $tracker = $this->loadTracker();
        $trackedPermissions = $tracker['permissions'] ?? [];

        $permissionsToCreate = array_diff($definedPermissions, $trackedPermissions);
        $permissionsToRemove = array_diff($trackedPermissions, $definedPermissions);

        // Create newly defined permissions
        foreach ($permissionsToCreate as $name) {
            Permission::firstOrCreate([
                'name'       => $name,
                'guard_name' => $guard,
            ]);
        }

        // Remove permissions that are no longer defined in this module
        if (!empty($permissionsToRemove)) {
            DB::beginTransaction();
            try {
                $perms = Permission::whereIn('name', $permissionsToRemove)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Booking Installer: Failed to remove obsolete permissions: ' . $e->getMessage());
                throw $e;
            }
        }

        // Optional module-owned roles (logic remains the same)
        $moduleRoles = [];
        $newlyCreatedRoles = [];
        foreach ($moduleRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            if ($role->wasRecentlyCreated) {
                $newlyCreatedRoles[] = $role->name;
            }
        }
        $tracker['roles'] = array_values(array_unique(array_merge($tracker['roles'] ?? [], $newlyCreatedRoles)));


        // Always grant all defined permissions to system roles
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($definedPermissions);
        }

        // Update tracker to match the new state
        $tracker['permissions'] = $definedPermissions;
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info('Booking Installer: permissions synced.');
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();

        parent::uninstall();

        Log::info('Booking Installer: uninstalled and permissions removed.');
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
            Log::error('Booking Installer: removeModuleOwnedPermissionsAndRoles failed: ' . $e->getMessage());
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
