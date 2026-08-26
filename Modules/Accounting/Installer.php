<?php

namespace Modules\Accounting;

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
    protected string $moduleName = 'Accounting';

    protected array $tables = [
        'accounting_proforma_items',
        'accounting_proformas',
        'accounting_invoice_payments',
        'accounting_invoice_items',
        'accounting_invoices',
        'cheque_attachments',
        'accounting_cheques',
        'accounting_transactions',
        'accounting_source_documents',
        'accounting_documents',
        'accounting_fund_accounts',
        'accounting_categories',
        'accounting_settings',
    ];

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
        Log::info('Accounting Installer: Starting custom reset process...');

        $this->cleanupDatabase();

        // 1. Execute the parent reset method
        parent::reset();
        Log::info('Accounting Installer: Parent reset completed.');

        // 2. Now, sync the permissions
        $this->syncPermissions();

        Log::info('Accounting Installer: Custom reset process finished.');
    }

    public function install(): void
    {
        Log::info('Accounting Installer: Starting install process...');
        $this->cleanupDatabase();

        parent::install();

        $this->syncPermissions();
        Log::info('Accounting Installer: Install process finished.');
    }

    public function uninstall(): void
    {
        parent::uninstall();
        Log::info('Accounting Installer: Starting uninstall process...');
        $this->cleanupDatabase();

        $trackerPath = $this->permissionsTrackerPath();
        if (!File::exists($trackerPath)) {
            Log::warning('Accounting Installer: Permission tracker not found on uninstall. Nothing to remove.');
            return;
        }

        $permissions = json_decode(File::get($trackerPath), true) ?: [];
        if (empty($permissions)) {
            return;
        }

        Log::info('Accounting Installer: Removing all module permissions...');
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
        Log::info('Accounting Installer: Uninstall process finished.');
    }

    private function cleanupDatabase(): void
    {
        Log::info('Accounting Installer: Cleaning up existing tables...');
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
                Log::info('Accounting Installer: Cleared migration records for Accounting module.');
            }
        } catch (\Throwable $e) {
            Log::warning('Accounting Installer: Failed to clear migration records: ' . $e->getMessage());
        }

        Log::info('Accounting Installer: Database cleanup finished.');
    }

    private function syncPermissions(): void
    {
        Log::info('Accounting Installer: Starting permission sync...');
        $guard = config('auth.defaults.guard', 'web');

        $definedPermissions = [
            'accounting.view',
            'accounting.manage',
            'accounting.dashboard.view',

            // سرفصل‌ها
            'accounting.categories.view',
            'accounting.categories.create',
            'accounting.categories.edit',
            'accounting.categories.delete',
            'accounting.categories.manage',

            // حساب‌های خزانه‌داری
            'accounting.fund_accounts.view',
            'accounting.fund_accounts.create',
            'accounting.fund_accounts.edit',
            'accounting.fund_accounts.delete',
            'accounting.fund_accounts.manage',

            // اسناد حسابداری
            'accounting.documents.view',
            'accounting.documents.create',
            'accounting.documents.edit',
            'accounting.documents.delete',
            'accounting.documents.manage',

            // تراکنش‌ها
            'accounting.transactions.view',

            // سیستم چک‌ها
            'accounting.cheques.view',
            'accounting.cheques.create',
            'accounting.cheques.edit',
            'accounting.cheques.delete',
            'accounting.cheques.action',
            'accounting.cheques.manage',

            // فاکتورها
            'accounting.invoices.view',
            'accounting.invoices.create',
            'accounting.invoices.edit',
            'accounting.invoices.delete',
            'accounting.invoices.manage',

            // پیش‌فاکتورها
            'accounting.proformas.view',
            'accounting.proformas.create',
            'accounting.proformas.edit',
            'accounting.proformas.delete',
            'accounting.proformas.manage',

            // هزینه‌ها
            'accounting.expenses.view',
            'accounting.expenses.create',
            'accounting.expenses.edit',
            'accounting.expenses.delete',
            'accounting.expenses.manage',

            // گزارشات و تنظیمات
            'accounting.reports.view',
            'accounting.settings.manage',
        ];

        $trackerPath = $this->permissionsTrackerPath();
        File::ensureDirectoryExists(dirname($trackerPath));
        $trackedPermissions = File::exists($trackerPath) ? json_decode(File::get($trackerPath), true) ?: [] : [];

        $permissionsToCreate = array_diff($definedPermissions, $trackedPermissions);
        $permissionsToRemove = array_diff($trackedPermissions, $definedPermissions);

        Log::info('Accounting Installer: Ensuring all defined permissions exist...');
        foreach ($definedPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        if (!empty($permissionsToRemove)) {
            Log::info('Accounting Installer: Removing permissions: ' . implode(', ', $permissionsToRemove));
            DB::transaction(function () use ($permissionsToRemove, $guard) {
                $perms = Permission::whereIn('name', $permissionsToRemove)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            });
        }

        if (empty($permissionsToCreate) && empty($permissionsToRemove)) {
            Log::info('Accounting Installer: Permissions are already up to date.');
        }

        Log::info('Accounting Installer: Syncing permissions with admin roles...');
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
        Log::info('Accounting Installer: Permission sync finished.');
    }

    private function trackerPath(): string
    {
        return storage_path('app/module-install-trackers/accounting.json');
    }

    private function permissionsTrackerPath(): string
    {
        return storage_path('app/module-install-trackers/accounting_permissions.json');
    }
}
