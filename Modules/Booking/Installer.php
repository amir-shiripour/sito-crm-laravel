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

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    /**
     * Overriding the reset method to hook our permission sync logic
     * after the parent's reset (which includes migrate-refresh).
     */
    public function reset(): void
    {
        Log::info('Booking Installer: Starting custom reset process...');

        // 1. Execute the parent reset method (handles migrate-refresh, seed, etc.)
        parent::reset();
        Log::info('Booking Installer: Parent reset completed.');

        // 2. Now, sync the permissions
        $this->syncPermissions();

        Log::info('Booking Installer: Custom reset process finished.');
    }

    public function install(): void
    {
        parent::install();
        Log::info('Booking Installer: Starting install process...');
        $this->syncPermissions();
        Log::info('Booking Installer: Install process finished.');
    }

    private function syncPermissions(): void
    {
        Log::info('Booking Installer: Starting permission sync...');
        $guard = config('auth.defaults.guard', 'web');

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
            'booking.statement.view',
            'booking.statement.view.all',
            'booking.statement.view.own',
            'booking.statement.create',
            'booking.statement.edit',
            'booking.statement.delete',
            'booking.statement.manage',
        ];

        $trackerPath = $this->permissionsTrackerPath();
        $trackedPermissions = File::exists($trackerPath) ? json_decode(File::get($trackerPath), true) ?: [] : [];

        $permissionsToCreate = array_diff($definedPermissions, $trackedPermissions);
        $permissionsToRemove = array_diff($trackedPermissions, $definedPermissions);

        if (!empty($permissionsToCreate)) {
            Log::info('Booking Installer: Creating permissions: ' . implode(', ', $permissionsToCreate));
            foreach ($permissionsToCreate as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }
        }

        if (!empty($permissionsToRemove)) {
            Log::info('Booking Installer: Removing permissions: ' . implode(', ', $permissionsToRemove));
            DB::transaction(function () use ($permissionsToRemove, $guard) {
                $perms = Permission::whereIn('name', $permissionsToRemove)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            });
        }

        if (empty($permissionsToCreate) && empty($permissionsToRemove)) {
            Log::info('Booking Installer: Permissions are already up to date.');
        }

        Log::info('Booking Installer: Syncing permissions with admin roles...');
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($definedPermissions);
        }

        File::put($trackerPath, json_encode($definedPermissions, JSON_PRETTY_PRINT));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Booking Installer: Permission sync finished.');
    }

    public function uninstall(): void
    {
        parent::uninstall();
        Log::info('Booking Installer: Starting uninstall process...');

        $trackerPath = $this->permissionsTrackerPath();
        if (!File::exists($trackerPath)) {
            Log::warning('Booking Installer: Permission tracker not found on uninstall. Nothing to remove.');
            return;
        }

        $permissions = json_decode(File::get($trackerPath), true) ?: [];
        if (empty($permissions)) {
            return;
        }

        Log::info('Booking Installer: Removing all module permissions...');
        DB::transaction(function () use ($permissions) {
            $guard = config('auth.defaults.guard', 'web');
            $perms = Permission::whereIn('name', $permissions)->where('guard_name', $guard)->get();
            foreach ($perms as $perm) {
                $perm->roles()->detach();
                $perm->delete();
            }
        });

        File::delete($this->trackerPath());
        File::delete($trackerPath);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Booking Installer: Uninstall process finished.');
    }

    // Helper methods for tracker paths
    private function trackerPath(): string
    {
        return storage_path('app/module-install-trackers/booking.json');
    }

    private function permissionsTrackerPath(): string
    {
        return storage_path('app/module-install-trackers/booking_permissions.json');
    }
}
