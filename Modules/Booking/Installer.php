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

        $perms = [
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

        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate([
                'name'       => $name,
                'guard_name' => $guard,
            ]);

            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        // Optional module-owned roles (leave empty to avoid role-name conflicts)
        $moduleRoles = [];

        foreach ($moduleRoles as $roleName) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => $guard,
            ]);

            if ($role->wasRecentlyCreated) {
                $tracker['roles'][] = $role->name;
            }
        }

        // Always grant to super-admin (same pattern as other modules)
        foreach (['super-admin','admin'] as $sysRole) {
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

        Log::info('Booking Installer: permissions created / roles updated.');
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
