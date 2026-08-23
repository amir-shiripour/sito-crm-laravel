<?php

namespace Modules\Projects;

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
    protected string $moduleName = 'Projects';

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
        Log::info('Projects Installer: Starting custom reset process...');

        $this->cleanupDatabase();

        // 1. Execute the parent reset method
        parent::reset();
        Log::info('Projects Installer: Parent reset completed.');

        // 2. Now, sync the permissions
        $this->syncPermissions();

        Log::info('Projects Installer: Custom reset process finished.');
    }

    public function install(): void
    {
        Log::info('Projects Installer: Starting install process...');
        $this->cleanupDatabase();

        parent::install();

        $this->syncPermissions();
        Log::info('Projects Installer: Install process finished.');
    }

    public function uninstall(): void
    {
        parent::uninstall();
        Log::info('Projects Installer: Starting uninstall process...');
        $this->cleanupDatabase();

        $trackerPath = $this->permissionsTrackerPath();
        if (!File::exists($trackerPath)) {
            Log::warning('Projects Installer: Permission tracker not found on uninstall. Nothing to remove.');
            return;
        }

        $permissions = json_decode(File::get($trackerPath), true) ?: [];
        if (empty($permissions)) {
            return;
        }

        Log::info('Projects Installer: Removing all module permissions...');
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
        Log::info('Projects Installer: Uninstall process finished.');
    }

    private function cleanupDatabase(): void
    {
        Log::info('Projects Installer: Cleaning up existing tables...');
        Schema::disableForeignKeyConstraints();

        $tables = [
            'projects_messages',
            'projects_documents',
            'projects_checklist_items',
            'projects_tasks',
            'projects_members',
            'projects',
            'projects_statuses',
            'projects_categories',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        // Enable foreign key constraints check
        Schema::enableForeignKeyConstraints();

        // Clear migration records for this module from the migrations table
        try {
            $migrationsPath = __DIR__ . '/Database/Migrations';
            if (File::isDirectory($migrationsPath)) {
                $files = File::files($migrationsPath);
                $migrationNames = [];
                foreach ($files as $file) {
                    $migrationNames[] = $file->getBasename('.php');
                }
                if (!empty($migrationNames)) {
                    DB::table('migrations')->whereIn('migration', $migrationNames)->delete();
                    Log::info('Projects Installer: Cleared migration records for Projects module.');
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Projects Installer: Failed to clear migration records: ' . $e->getMessage());
        }

        Log::info('Projects Installer: Database cleanup finished.');
    }

    private function syncPermissions(): void
    {
        Log::info('Projects Installer: Starting permission sync...');
        $guard = config('auth.defaults.guard', 'web');

        $definedPermissions = [
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.cancel',
            'projects.manage',
            'projects.categories.manage',
            'projects.status-builder.manage',
            'projects.settings.manage',
        ];

        $trackerPath = $this->permissionsTrackerPath();
        File::ensureDirectoryExists(dirname($trackerPath));
        $trackedPermissions = File::exists($trackerPath) ? json_decode(File::get($trackerPath), true) ?: [] : [];

        $permissionsToCreate = array_diff($definedPermissions, $trackedPermissions);
        $permissionsToRemove = array_diff($trackedPermissions, $definedPermissions);

        Log::info('Projects Installer: Ensuring all defined permissions exist...');
        foreach ($definedPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        if (!empty($permissionsToRemove)) {
            Log::info('Projects Installer: Removing permissions: ' . implode(', ', $permissionsToRemove));
            DB::transaction(function () use ($permissionsToRemove, $guard) {
                $perms = Permission::whereIn('name', $permissionsToRemove)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            });
        }

        if (empty($permissionsToCreate) && empty($permissionsToRemove)) {
            Log::info('Projects Installer: Permissions are already up to date.');
        }

        Log::info('Projects Installer: Syncing permissions with admin roles...');
        $roleDisplayNames = [
            'super-admin' => 'مدیر کل',
            'admin' => 'مدیر',
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
        Log::info('Projects Installer: Permission sync finished.');
    }

    // Helper methods for tracker paths
    private function trackerPath(): string
    {
        return storage_path('app/module-install-trackers/projects.json');
    }

    private function permissionsTrackerPath(): string
    {
        return storage_path('app/module-install-trackers/projects_permissions.json');
    }
}
