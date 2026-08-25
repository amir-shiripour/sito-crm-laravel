<?php

namespace Modules\Services;

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
    protected string $moduleName = 'Services';

    protected array $tables = [
        'service_invoice_payments',
        'service_invoice_items',
        'service_invoices',
        'service_orders',
        'services_projects',
        'services_activity_log',
        'services_custom_field_values',
        'services_custom_fields',
        'services',
        'service_templates',
        'services_catalog',
        'service_categories',
        'services_statuses',
        'service_packages',
        'service_package_items',
    ];

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
        parent::__construct($this->moduleName);
    }

    /**
     * Overriding the reset method to hook our permission sync logic
     * after the parent's reset (which includes migrate-refresh).
     */
    public function reset(): void
    {
        Log::info('Services Installer: Starting custom reset process...');

        $this->cleanupDatabase();

        // 1. Execute the parent reset method
        parent::reset();
        Log::info('Services Installer: Parent reset completed.');

        // 2. Now, sync the permissions
        $this->syncPermissions();

        Log::info('Services Installer: Custom reset process finished.');
    }

    public function install(): void
    {
        Log::info('Services Installer: Starting install process...');
        $this->cleanupDatabase();

        parent::install();

        $this->syncPermissions();
        Log::info('Services Installer: Install process finished.');
    }

    public function uninstall(): void
    {
        parent::uninstall();
        Log::info('Services Installer: Starting uninstall process...');
        $this->cleanupDatabase();

        $tracker = $this->loadTracker();
        $permissions = $tracker['permissions'] ?? [];

        if (!empty($permissions)) {
            Log::info('Services Installer: Removing all module permissions...');
            DB::transaction(function () use ($permissions) {
                $guard = config('auth.defaults.guard', 'web');
                $perms = Permission::whereIn('name', $permissions)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            });
        }

        File::delete($this->trackerPath());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Services Installer: Uninstall process finished.');
    }
    private function cleanupDatabase(): void
    {
        Log::info('Services Installer: Cleaning up existing tables...');
        Schema::disableForeignKeyConstraints();

        $tables = [
            'service_invoice_payments',
            'service_invoice_items',
            'service_invoices',
            'service_orders',
            'services_projects',
            'services_activity_log',
            'services_custom_field_values',
            'services_custom_fields',
            'services',
            'service_templates',
            'services_catalog',
            'service_categories',
            'services_statuses',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        // فعال کردن مجدد بررسی کلیدهای خارجی
        Schema::enableForeignKeyConstraints();

        // پاک کردن سوابق مایگریشن‌های این ماژول از جدول migrations برای جلوگیری از بروز خطا در پس‌روی (Rollback)
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
                    Log::info('Services Installer: Cleared migration records for Services module.');
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Services Installer: Failed to clear migration records: ' . $e->getMessage());
        }

        Log::info('Services Installer: Database cleanup finished.');
    }

    private function syncPermissions(): void
    {
        Log::info('Services Installer: Starting permission sync...');
        $guard = config('auth.defaults.guard', 'web');

        $definedPermissions = [
            'services.view',
            'services.manage',
            'services.create',
            'services.edit',
            'services.delete',
            'services.duplicate',

            // فاکتورها
            'services.invoices.view',
            'services.invoices.view.all',
            'services.invoices.create',
            'services.invoices.edit',
            'services.invoices.delete',
            'services.invoices.manage',
            'services.invoices.pay',

            // تنظیمات و مدیریت وضعیت‌ها
            'status-builder.manage',
            'services.settings.manage',
        ];

        $trackerPath = $this->permissionsTrackerPath();
        File::ensureDirectoryExists(dirname($trackerPath));
        $trackedPermissions = File::exists($trackerPath) ? json_decode(File::get($trackerPath), true) ?: [] : [];

        $permissionsToCreate = array_diff($definedPermissions, $trackedPermissions);
        $permissionsToRemove = array_diff($trackedPermissions, $definedPermissions);

        Log::info('Services Installer: Ensuring all defined permissions exist...');
        foreach ($definedPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        if (!empty($permissionsToRemove)) {
            Log::info('Services Installer: Removing permissions: ' . implode(', ', $permissionsToRemove));
            DB::transaction(function () use ($permissionsToRemove, $guard) {
                $perms = Permission::whereIn('name', $permissionsToRemove)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            });
        }

        if (empty($permissionsToCreate) && empty($permissionsToRemove)) {
            Log::info('Services Installer: Permissions are already up to date.');
        }

        Log::info('Services Installer: Syncing permissions with admin roles...');
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

        $tracker = $this->loadTracker();
        $tracker['permissions'] = array_values(array_unique(array_merge($tracker['permissions'] ?? [], $definedPermissions)));
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Services Installer: Permission sync finished.');
    }
}
