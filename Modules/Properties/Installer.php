<?php

namespace Modules\Properties;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected string $moduleName = 'Properties';

    protected array $tables = [
        'property_statuses',
        'properties',
        'property_categories',
        'property_images',
        'property_settings',
        'property_attributes',
        'property_attribute_values',
        'property_owners',
        'property_buildings',
    ];

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    /**
     * Clean up orphaned migration records if migrations are marked as completed in the database
     * but the corresponding tables do NOT exist.
     *
     * IMPORTANT: Never drops existing tables or modifies existing data.
     */
    protected function cleanOrphanedModuleMigrations(): void
    {
        try {
            if (!Schema::hasTable('migrations')) {
                return;
            }

            // Map each target table to its corresponding migration files
            $tableMigrationMap = [
                'property_statuses' => [
                    '2025_02_20_000000_create_property_statuses_table',
                    '2026_06_21_000000_add_show_in_crm_to_property_statuses_table',
                ],
                'properties' => [
                    '2025_02_20_000100_create_properties_table',
                    '2025_02_20_000500_add_document_type_to_properties',
                    '2025_02_20_000600_add_video_to_properties',
                    '2025_02_20_000800_add_convertible_fields_to_properties',
                    '2025_02_20_001100_add_owner_id_to_properties_table',
                    '2025_02_20_001200_add_building_id_to_properties_table',
                    '2025_02_20_001300_add_file_info_fields_to_properties_table',
                    '2025_02_21_000000_add_agent_id_to_properties_table',
                ],
                'property_categories' => [
                    '2025_02_20_000200_create_property_categories_table',
                ],
                'property_images' => [
                    '2025_02_20_000400_create_property_images_table',
                ],
                'property_settings' => [
                    '2025_02_20_000500_add_upload_settings',
                ],
                'property_attributes' => [
                    '2025_02_20_000900_create_property_attributes_tables',
                    '2025_02_20_001400_add_filter_settings_to_property_attributes',
                ],
                'property_attribute_values' => [
                    '2025_02_20_000900_create_property_attributes_tables',
                ],
                'property_owners' => [
                    '2025_02_20_001000_create_property_owners_table',
                ],
                'property_buildings' => [
                    '2025_02_20_001500_create_property_buildings_table',
                ],
            ];

            $migrationsToDelete = [];
            foreach ($tableMigrationMap as $table => $migrations) {
                // ONLY if the table does NOT exist in the database, clear its recorded migrations so migrate can recreate it
                if (!Schema::hasTable($table)) {
                    foreach ($migrations as $m) {
                        $migrationsToDelete[] = $m;
                    }
                }
            }

            if (!empty($migrationsToDelete)) {
                $migrationsToDelete = array_values(array_unique($migrationsToDelete));
                DB::table('migrations')->whereIn('migration', $migrationsToDelete)->delete();
                Log::info('Properties Installer: Cleared orphaned migration records for missing tables: ' . implode(', ', $migrationsToDelete));
            }
        } catch (\Throwable $e) {
            Log::warning('Properties Installer: cleanOrphanedModuleMigrations error: ' . $e->getMessage());
        }
    }

    public function reset(): void
    {
        Log::info('Properties Installer: Starting custom reset process...');
        parent::reset();
        Log::info('Properties Installer: Parent reset completed.');
        $this->ensureTablesExist();
        $this->syncPermissions();
        $this->seedDefaultStatuses();
        $this->markInstalledTracker();
        Log::info('Properties Installer: Custom reset process finished.');
    }

    public function install(): void
    {
        Log::info('Properties Installer: Starting install process...');
        
        // 1. Clean up any orphaned migrations for tables that do not exist
        $this->cleanOrphanedModuleMigrations();

        // 2. Run standard parent install (migrations, seeders, enable, optimize:clear)
        parent::install();

        // 3. Ensure tables exist (fallback if module:migrate didn't create property_statuses)
        $this->ensureTablesExist();

        // 4. Sync permissions & seed statuses
        $this->syncPermissions();
        $this->seedDefaultStatuses();

        // 5. Write tracker file so BaseModuleInstaller::isInstalled('Properties') is true
        $this->markInstalledTracker();

        Log::info('Properties Installer: Install process finished.');
    }

    /**
     * Self-healing verification to ensure critical module tables exist.
     */
    protected function ensureTablesExist(): void
    {
        if (!Schema::hasTable('property_statuses')) {
            Log::info('Properties Installer: property_statuses table missing after parent::install, running migrations explicitly...');
            try {
                $this->runArtisan('migrate', [
                    '--path' => 'Modules/Properties/Database/Migrations',
                    '--force' => true,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Properties Installer: explicit migrate failed: ' . $e->getMessage());
            }
        }

        // Ultimate safety check: if property_statuses is still missing, run its migration file directly
        if (!Schema::hasTable('property_statuses')) {
            Log::info('Properties Installer: property_statuses still missing, executing migration file directly...');
            $migrationFile = module_path('Properties', 'Database/Migrations/2025_02_20_000000_create_property_statuses_table.php');
            if (File::exists($migrationFile)) {
                $migration = require $migrationFile;
                if (is_object($migration) && method_exists($migration, 'up')) {
                    $migration->up();
                    if (Schema::hasTable('migrations')) {
                        DB::table('migrations')->updateOrInsert(
                            ['migration' => '2025_02_20_000000_create_property_statuses_table'],
                            ['batch' => (DB::table('migrations')->max('batch') ?: 0) + 1]
                        );
                    }
                }
            }

            $alterMigrationFile = module_path('Properties', 'Database/Migrations/2026_06_21_000000_add_show_in_crm_to_property_statuses_table.php');
            if (File::exists($alterMigrationFile)) {
                $alterMigration = require $alterMigrationFile;
                if (is_object($alterMigration) && method_exists($alterMigration, 'up')) {
                    $alterMigration->up();
                    if (Schema::hasTable('migrations')) {
                        DB::table('migrations')->updateOrInsert(
                            ['migration' => '2026_06_21_000000_add_show_in_crm_to_property_statuses_table'],
                            ['batch' => (DB::table('migrations')->max('batch') ?: 0) + 1]
                        );
                    }
                }
            }
        }
    }

    private function seedDefaultStatuses(): void
    {
        Log::info('Properties Installer: Seeding default statuses...');

        if (!Schema::hasTable('property_statuses')) {
            Log::warning('Properties Installer: property_statuses table does not exist, skipping status seeding.');
            return;
        }

        \Modules\Properties\Entities\PropertyStatus::firstOrCreate(
            ['key' => 'new'],
            [
                'label' => 'جدید',
                'color' => '#10b981',
                'is_system' => true,
                'is_active' => true,
                'is_default' => true,
                'show_in_crm' => true,
                'sort_order' => 1
            ]
        );
    }

    public function createPermissions(): void
    {
        $this->syncPermissions();
    }

    public function syncPermissions(): void
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

        File::ensureDirectoryExists(dirname($trackerPath));
        File::put($trackerPath, json_encode($definedPermissions, JSON_PRETTY_PRINT));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Properties Installer: Permission sync finished.');
    }

    protected function markInstalledTracker(): void
    {
        $trackerPath = $this->trackerPath();
        File::ensureDirectoryExists(dirname($trackerPath));
        File::put($trackerPath, json_encode([
            'module' => $this->moduleName,
            'installed_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));
    }

    public function uninstall(): void
    {
        parent::uninstall();
        Log::info('Properties Installer: Starting uninstall process...');

        $trackerPath = $this->permissionsTrackerPath();
        if (File::exists($trackerPath)) {
            $permissions = json_decode(File::get($trackerPath), true) ?: [];
            if (!empty($permissions)) {
                Log::info('Properties Installer: Removing all module permissions...');
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
        Log::info('Properties Installer: Uninstall process finished.');
    }

    // Helper methods for tracker paths
    public function trackerPath(): string
    {
        return storage_path('app/module-installer/' . $this->moduleSlug . '/created.json');
    }

    public function permissionsTrackerPath(): string
    {
        return storage_path('app/module-install-trackers/properties_permissions.json');
    }
}
