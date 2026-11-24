<?php

namespace Modules\Warehouse;

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
    protected string $moduleName = 'Warehouse';

    protected array $tables = [
        'warehouse_item_stocks',
        'warehouse_item_batches',
        'warehouse_items',
        'warehouse_categories',
        'warehouse_managers',
        'warehouse_market_links',
        'warehouse_settings',
        'warehouse_warehouses',
    ];

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    public function install(): void
    {
        Log::info('Warehouse Installer: Starting install process...');
        $this->cleanupDatabase();

        parent::install();

        $this->syncPermissions();
        Log::info('Warehouse Installer: Install process finished.');
    }

    public function reset(): void
    {
        Log::info('Warehouse Installer: Starting custom reset process...');
        $this->cleanupDatabase();

        parent::reset();

        $this->syncPermissions();
        Log::info('Warehouse Installer: Custom reset process finished.');
    }

    public function uninstall(): void
    {
        parent::uninstall();
        Log::info('Warehouse Installer: Starting uninstall process...');
        $this->cleanupDatabase();

        $trackerPath = $this->permissionsTrackerPath();
        if (File::exists($trackerPath)) {
            $permissions = json_decode(File::get($trackerPath), true) ?: [];
            if (!empty($permissions)) {
                Log::info('Warehouse Installer: Removing all module permissions...');
                DB::transaction(function () use ($permissions) {
                    $guard = config('auth.defaults.guard', 'web');
                    $perms = Permission::whereIn('name', $permissions)->where('guard_name', $guard)->get();
                    foreach ($perms as $perm) {
                        $perm->roles()->detach();
                        $perm->delete();
                    }
                });
            }
            File::delete($trackerPath);
        }

        File::delete($this->trackerPath());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Warehouse Installer: Uninstall process finished.');
    }

    private function cleanupDatabase(): void
    {
        Log::info('Warehouse Installer: Cleaning up existing tables...');
        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        try {
            $candidates = [
                __DIR__ . '/Database/Migrations',
                __DIR__ . '/database/migrations',
            ];

            $migrationNames = [];
            foreach ($candidates as $migrationsPath) {
                if (File::isDirectory($migrationsPath)) {
                    $files = File::files($migrationsPath);
                    foreach ($files as $file) {
                        $migrationNames[] = $file->getBasename('.php');
                    }
                }
            }

            if (!empty($migrationNames)) {
                $migrationNames = array_unique($migrationNames);
                DB::table('migrations')->whereIn('migration', $migrationNames)->delete();
                Log::info('Warehouse Installer: Cleared migration records for Warehouse module.');
            }
        } catch (\Throwable $e) {
            Log::warning('Warehouse Installer: Failed to clear migration records: ' . $e->getMessage());
        }

        Log::info('Warehouse Installer: Database cleanup finished.');
    }

    private function syncPermissions(): void
    {
        Log::info('Warehouse Installer: Starting permission sync...');
        $guard = config('auth.defaults.guard', 'web');

        $definedPermissions = [
            // General
            'warehouse.view',
            'warehouse.manage',
            'warehouse.dashboard.view',
            'warehouse.settings.manage',

            // Inventory
            'warehouse.inventory.view',
            'warehouse.inventory.create',
            'warehouse.inventory.edit',
            'warehouse.inventory.delete',
            'warehouse.inventory.manage',

            // Assets
            'warehouse.assets.view',
            'warehouse.assets.create',
            'warehouse.assets.edit',
            'warehouse.assets.delete',
            'warehouse.assets.manage',

            // Market Integration
            'warehouse.market_integration.manage',
        ];

        $trackerPath = $this->permissionsTrackerPath();
        File::ensureDirectoryExists(dirname($trackerPath));
        $trackedPermissions = File::exists($trackerPath) ? json_decode(File::get($trackerPath), true) ?: [] : [];

        $permissionsToRemove = array_diff($trackedPermissions, $definedPermissions);

        foreach ($definedPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        if (!empty($permissionsToRemove)) {
            DB::transaction(function () use ($permissionsToRemove, $guard) {
                $perms = Permission::whereIn('name', $permissionsToRemove)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            });
        }

        // Grant all permissions to super-admin and admin
        $roleDisplayNames = [
            'super-admin' => 'مدیر کل',
            'admin'       => 'مدیر',
        ];
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(
                ['name' => $sysRole, 'guard_name' => $guard],
                ['display_name' => $roleDisplayNames[$sysRole] ?? $sysRole]
            );
            $role->givePermissionTo($definedPermissions);
        }

        File::put($trackerPath, json_encode($definedPermissions, JSON_PRETTY_PRINT));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Warehouse Installer: Permission sync finished.');
    }

    private function trackerPath(): string
    {
        return storage_path('app/module-install-trackers/warehouse.json');
    }

    private function permissionsTrackerPath(): string
    {
        return storage_path('app/module-install-trackers/warehouse_permissions.json');
    }
}
