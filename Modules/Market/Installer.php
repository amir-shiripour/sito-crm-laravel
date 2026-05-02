<?php

namespace Modules\Market;

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
    // 💡 معرفی تمام جداول ماژول به BaseModuleInstaller برای اجرای صحیح بک‌آپ‌گیری
    protected array $tables = [
        'market_settings',
        'market_brands',
        'market_categories',
        'market_master_products',
        'market_vendors',
        'market_vendor_addresses',
        'market_vendor_documents',
        'market_vendor_products',
        'market_orders',
        'market_order_items',
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
        parent::__construct('Market');
    }

    public function install(): void
    {
        parent::install();

        $guard = config('auth.defaults.guard', 'web');

        // ----- Permissions -----
        $perms = [
            'market.manage' => 'مدیریت کلان فروشگاه (Super Admin)',

            'market.products.view' => 'مشاهده محصولات',
            'market.products.create' => 'ایجاد محصول',
            'market.products.edit' => 'ویرایش محصول',
            'market.products.delete' => 'حذف محصول',

            'market.orders.view' => 'مشاهده سفارشات',
            'market.orders.manage' => 'مدیریت سفارشات',

            'market.categories.manage' => 'مدیریت کاتالوگ و دسته‌بندی‌ها',

            'market.vendors.view' => 'مشاهده فروشندگان',
            'market.vendors.manage' => 'مدیریت فروشندگان',
        ];

        $tracker = $this->loadTracker();

        foreach ($perms as $name => $displayName) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                ['display_name' => $displayName]
            );
            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        $moduleRoles = [
            'vendor' => 'فروشنده'
        ];

        foreach ($moduleRoles as $rname => $displayName) {
            $role = Role::firstOrCreate(
                ['name' => $rname, 'guard_name' => $guard],
                ['display_name' => $displayName]
            );
            if ($role->wasRecentlyCreated) {
                $tracker['roles'][] = $role->name;
            }
        }

        $adminRoles = [
            'super-admin' => 'مدیر کل',
            'admin' => 'مدیر'
        ];

        foreach ($adminRoles as $sysRole => $displayName) {
            $role = Role::firstOrCreate(
                ['name' => $sysRole, 'guard_name' => $guard],
                ['display_name' => $displayName]
            );
            $role->givePermissionTo(array_keys($perms));
        }

        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles']       = array_values(array_unique($tracker['roles']));
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info("Market Installer: permissions created / roles updated.");
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();

        // 💡 پاکسازی امن دیتابیس: جلوگیری از ارورهای Foreign Key و Table Already Exists هنگام ریست ماژول
        try {
            Schema::disableForeignKeyConstraints();
            foreach ($this->tables as $table) {
                Schema::dropIfExists($table);
            }
            Schema::enableForeignKeyConstraints();

            // پاک کردن تاریخچه مایگریشن‌های ماژول مارکت تا لاراول با لوح سفید شروع کند
            DB::table('migrations')->where('migration', 'like', '%market%')->delete();
        } catch (\Throwable $e) {
            Log::error("Market Installer DB Cleanup failed: " . $e->getMessage());
        }

        parent::uninstall();
        Log::info("Market Installer: uninstalled, permissions and tables safely removed.");
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            foreach ($tracker['roles'] ?? [] as $roleName) {
                if (! $roleName) continue;
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if ($role) {
                    $role->permissions()->detach();
                    $role->delete();
                }
            }

            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (! $permName) continue;
                $perm = Permission::where('name', $permName)->where('guard_name', $guard)->first();
                if ($perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Market Installer: removeModuleOwnedPermissionsAndRoles failed: ".$e->getMessage());
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
