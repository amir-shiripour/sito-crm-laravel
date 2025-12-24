<?php

namespace Modules\Workflows;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    public function __construct()
    {
        parent::__construct('Workflows');
    }

    /**
     * مسیر فایل tracker مخصوص این ماژول
     */
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
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * نصب ماژول: ساخت پرمیژن‌ها، نقش‌های اختصاصی و اتصال به نقش‌های سراسری
     */
    public function install(): void
    {
        parent::install();

        $guard = config('auth.defaults.guard', 'web');

        // لیست پرمیژن‌های ماژول Workflows
        $perms = [
            'workflows.view',
            'workflows.manage',
            'workflows.run',
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

        // اگر برای ماژول نقش اختصاصی خواستیم، اینجا اضافه می‌کنیم
        $moduleRoles = [
            // 'workflow-manager',
        ];

        foreach ($moduleRoles as $rname) {
            $role = Role::firstOrCreate([
                'name'       => $rname,
                'guard_name' => $guard,
            ]);

            if ($role->wasRecentlyCreated) {
                $tracker['roles'][] = $role->name;
            }

            $role->givePermissionTo($perms);
        }

        // نقش‌های سراسری که باید همهٔ دسترسی‌های این ماژول را داشته باشند
        foreach (['super-admin'] as $sysRole) {
            $role = Role::firstOrCreate([
                'name'       => $sysRole,
                'guard_name' => $guard,
            ]);
            $role->givePermissionTo($perms);
        }

        // یکتا کردن و ذخیره tracker
        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles']       = array_values(array_unique($tracker['roles']));
        $this->saveTracker($tracker);

        // پاک کردن کش Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info('Workflows Installer: permissions created / roles updated.');
    }

    /**
     * آن‌اینستال ماژول: حذف پرمیژن‌ها و نقش‌های اختصاصی
     */
    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();

        parent::uninstall();

        Log::info('Workflows Installer: uninstalled and permissions removed.');
    }

    /**
     * حذف permission و role هایی که توسط این ماژول ساخته شده‌اند
     */
    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard   = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            // حذف role های اختصاصی ماژول
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

            // حذف permission های ساخته‌شده توسط ماژول
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
            Log::error('Workflows Installer: removeModuleOwnedPermissionsAndRoles failed: '.$e->getMessage());
            throw $e;
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        // حذف فایل tracker تا نصب بعدی از صفر انجام شود
        $path = $this->trackerPath();
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
