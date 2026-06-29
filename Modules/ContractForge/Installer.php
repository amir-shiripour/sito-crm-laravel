<?php

namespace Modules\ContractForge;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected function trackerPath(): string
    {
        return storage_path('app/module-installer/' . $this->moduleSlug . '/created.json');
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
        File::put(
            $path,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    public function __construct()
    {
        parent::__construct('ContractForge');
    }

    /**
     * Install ContractForge module
     */
    public function install(): void
    {
        parent::install();
        $this->createPermissionsAndRoles();
    }

    /**
     * Reset ContractForge module
     */
    public function reset(): void
    {
        parent::reset();
        $this->createPermissionsAndRoles();
        Log::info("ContractForge Installer: reset completed with permissions recreated.");
    }

    /**
     * Create permissions & roles setup
     */
    protected function createPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $perms = [
            'contractforge.view',            // View contracts & templates
            'contractforge.manage',          // Create, edit, cancel contracts & templates
            'contractforge.settings.manage', // Configure settings
        ];

        $tracker = $this->loadTracker();

        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );

            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        // Give permissions to admin roles
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($perms);
        }

        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles'] = array_values(array_unique($tracker['roles'] ?? []));

        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info("ContractForge Installer: permissions created.");
    }

    /**
     * Uninstall ContractForge module
     */
    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();
        parent::uninstall();
        Log::info("ContractForge Installer: uninstalled and permissions removed.");
    }

    /**
     * Clean up permissions
     */
    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();

        try {
            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (!$permName) {
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
            Log::error("ContractForge Installer: removeModuleOwnedPermissionsAndRoles failed: " . $e->getMessage());
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
