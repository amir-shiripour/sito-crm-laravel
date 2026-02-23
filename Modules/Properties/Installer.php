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
    protected string $moduleName = 'Properties';

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    public function reset(): void
    {
        Log::info('Properties Installer: Starting custom reset process...');
        parent::reset();
        Log::info('Properties Installer: Parent reset completed.');
        $this->syncPermissions();
        Log::info('Properties Installer: Custom reset process finished.');
    }

    public function install(): void
    {
        parent::install();
        Log::info('Properties Installer: Starting install process...');
        $this->syncPermissions();
        Log::info('Properties Installer: Install process finished.');
    }

    private function syncPermissions(): void
    {
        Log::info('Properties Installer: Starting permission sync...');
        $guard = config('auth.defaults.guard', 'web');

        $definedPermissions = [
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

            // Buildings
            'properties.buildings.view',
            'properties.buildings.create',
            'properties.buildings.edit',
            'properties.buildings.delete',
            'properties.buildings.manage',
        ];

        $trackerPath = $this->permissionsTrackerPath();
        $trackedPermissions = File::exists($trackerPath) ? json_decode(File::get($trackerPath), true) ?: [] : [];

        $permissionsToCreate = array_diff($definedPermissions, $trackedPermissions);
        $permissionsToRemove = array_diff($trackedPermissions, $definedPermissions);

        if (!empty($permissionsToCreate)) {
            Log::info('Properties Installer: Creating permissions: ' . implode(', ', $permissionsToCreate));
            foreach ($permissionsToCreate as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }
        }

        if (!empty($permissionsToRemove)) {
            Log::info('Properties Installer: Removing permissions: ' . implode(', ', $permissionsToRemove));
            DB::transaction(function () use ($permissionsToRemove, $guard) {
                $perms = Permission::whereIn('name', $permissionsToRemove)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            });
        }

        if (empty($permissionsToCreate) && empty($permissionsToRemove)) {
            Log::info('Properties Installer: Permissions are already up to date.');
        }

        Log::info('Properties Installer: Syncing permissions with admin roles...');
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($definedPermissions);
        }

        File::put($trackerPath, json_encode($definedPermissions, JSON_PRETTY_PRINT));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Properties Installer: Permission sync finished.');
    }

    public function uninstall(): void
    {
        parent::uninstall();
        Log::info('Properties Installer: Starting uninstall process...');

        $trackerPath = $this->permissionsTrackerPath();
        if (!File::exists($trackerPath)) {
            Log::warning('Properties Installer: Permission tracker not found on uninstall. Nothing to remove.');
            return;
        }

        $permissions = json_decode(File::get($trackerPath), true) ?: [];
        if (empty($permissions)) {
            return;
        }

        Log::info('Properties Installer: Removing all module permissions...');
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
        Log::info('Properties Installer: Uninstall process finished.');
    }

    // Helper methods for tracker paths
    private function trackerPath(): string
    {
        return storage_path('app/module-install-trackers/properties.json');
    }

    private function permissionsTrackerPath(): string
    {
        return storage_path('app/module-install-trackers/properties_permissions.json');
    }
}
