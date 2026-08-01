<?php

namespace Modules\Wallet;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected string $moduleName = 'Wallet';

    protected array $tables = [
        'wallets',
        'wallet_transactions',
    ];

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    public function reset(): void
    {
        Log::info('Wallet Installer: Starting custom reset process...');
        parent::reset();
        $this->syncPermissions();
        Log::info('Wallet Installer: Custom reset process finished.');
    }

    public function install(): void
    {
        parent::install();
        Log::info('Wallet Installer: Starting install process...');
        $this->syncPermissions();
        Log::info('Wallet Installer: Install process finished.');
    }

    public function syncPermissions(): void
    {
        Log::info('Wallet Installer: Starting permission sync...');
        $guard = config('auth.defaults.guard', 'web');

        $definedPermissions = [
            'wallet.view',
            'wallet.manage',
            'wallet.deposit',
            'wallet.withdraw',
            'wallet.transactions.view',
        ];

        $trackerPath = $this->permissionsTrackerPath();
        $trackedPermissions = File::exists($trackerPath) ? json_decode(File::get($trackerPath), true) ?: [] : [];

        $permissionsToCreate = array_diff($definedPermissions, $trackedPermissions);
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

        Log::info('Wallet Installer: Syncing permissions with admin roles (super-admin & admin)...');
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($definedPermissions);
        }

        File::ensureDirectoryExists(dirname($trackerPath));
        File::put($trackerPath, json_encode($definedPermissions, JSON_PRETTY_PRINT));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Wallet Installer: Permission sync finished.');
    }

    public function uninstall(): void
    {
        parent::uninstall();
        Log::info('Wallet Installer: Starting uninstall process...');

        $trackerPath = $this->permissionsTrackerPath();
        if (File::exists($trackerPath)) {
            $permissions = json_decode(File::get($trackerPath), true) ?: [];
            if (!empty($permissions)) {
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('Wallet Installer: Uninstall process finished.');
    }

    private function permissionsTrackerPath(): string
    {
        return storage_path('app/module-install-trackers/wallet_permissions.json');
    }
}
