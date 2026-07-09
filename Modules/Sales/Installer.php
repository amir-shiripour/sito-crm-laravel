<?php

namespace Modules\Sales;

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
    protected array $tables = [
        'sales_campaigns',
        'sales_campaign_contacts',
        'sales_campaign_results',
        'sales_calls',
        'sales_follow_ups',
        'sales_pipelines',
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
        parent::__construct('Sales');
    }

    public function install(): void
    {
        parent::install();

        $guard = config('auth.defaults.guard', 'web');

        $perms = [
            'sales.view' => 'مشاهده ماژول فروش',
            'sales.cockpit.view' => 'دسترسی به میز کار فروش',
            'sales.calls.view' => 'مشاهده تماس‌ها',
            'sales.calls.view.all' => 'همه تماس‌ها',
            'sales.calls.view.own' => 'فقط تماس‌های خود',
            'sales.calls.create' => 'ثبت تماس',
            'sales.calls.edit' => 'ویرایش تماس',
            'sales.calls.delete' => 'حذف تماس',
            'sales.calls.manage' => 'مدیریت تماس‌ها',
            'sales.followups.view' => 'مشاهده پیگیری‌ها',
            'sales.followups.view.all' => 'همه پیگیری‌ها',
            'sales.followups.view.own' => 'فقط پیگیری‌های خود',
            'sales.followups.create' => 'ثبت پیگیری',
            'sales.followups.edit' => 'ویرایش پیگیری',
            'sales.followups.delete' => 'حذف پیگیری',
            'sales.followups.manage' => 'مدیریت پیگیری‌ها',
            'sales.campaigns.view' => 'مشاهده کمپین‌ها',
            'sales.campaigns.create' => 'ایجاد کمپین',
            'sales.campaigns.edit' => 'ویرایش کمپین',
            'sales.campaigns.delete' => 'حذف کمپین',
            'sales.campaigns.manage' => 'مدیریت کمپین‌ها',
            'sales.campaigns.export' => 'خروجی گزارش کمپین',
            'sales.reports.view' => 'مشاهده گزارشات فروش',
            'sales.manage' => 'مدیریت تنظیمات فروش',
            'sales.deals.view' => 'مشاهده پرونده‌های فروش',
            'sales.deals.view.all' => 'همه پرونده‌های فروش',
            'sales.deals.view.own' => 'فقط پرونده‌های خود',
            'sales.deals.create' => 'ایجاد پرونده فروش',
            'sales.deals.edit' => 'ویرایش پرونده فروش',
            'sales.deals.delete' => 'حذف پرونده فروش',
            'sales.deals.manage' => 'مدیریت پرونده‌های فروش',
            'sales.pipelines.view' => 'مشاهده بورد کانبان',
            'sales.leads.view' => 'مشاهده مدیریت سرنخ‌ها',
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

        // Assign to admins
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
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info("Sales Installer: permissions created.");
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();

        try {
            Schema::disableForeignKeyConstraints();
            foreach ($this->tables as $table) {
                Schema::dropIfExists($table);
            }
            Schema::enableForeignKeyConstraints();

            DB::table('migrations')->where('migration', 'like', '%sales%')->delete();
        } catch (\Throwable $e) {
            Log::error("Sales Installer DB Cleanup failed: " . $e->getMessage());
        }

        parent::uninstall();
        Log::info("Sales Installer: uninstalled, permissions and tables safely removed.");
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
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
            Log::error("Sales Installer: removeModuleOwnedPermissionsAndRoles failed: ".$e->getMessage());
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
