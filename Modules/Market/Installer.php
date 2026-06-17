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
        'market_order_statuses',
        'market_order_items',
        'market_warehouses',
        'market_warehouse_stocks',
        'market_warehouse_transactions',
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
            'market.dashboard.view' => 'مشاهده داشبورد فروشگاه',

            'market.master-products.manage' => 'مدیریت کاتالوگ محصولات مرجع (Catalog)',

            'market.products.view' => 'مشاهده محصولات',
            'market.products.create' => 'ایجاد محصول',
            'market.products.edit' => 'ویرایش محصول',
            'market.products.delete' => 'حذف محصول',

            'market.orders.view' => 'مشاهده سفارشات',
            'market.orders.manage' => 'مدیریت سفارشات',

            'market.categories.manage' => 'مدیریت کاتالوگ و دسته‌بندی‌ها',

            'market.vendors.view' => 'مشاهده فروشندگان',
            'market.vendors.manage' => 'مدیریت فروشندگان',

            'market.warehouses.view' => 'مشاهده انبارها',
            'market.warehouses.manage' => 'مدیریت انبارها',

            'market.shipping.manage' => 'مدیریت حمل و نقل و ارسال',
            'market.questions.manage' => 'مدیریت پرسش‌ها و پاسخ‌ها',
            'market.reviews.manage' => 'مدیریت دیدگاه‌ها',
            'market.brands.manage' => 'مدیریت برندها',
            'market.attributes.manage' => 'مدیریت ویژگی‌های تنوع‌ساز',
            'market.settings.manage' => 'مدیریت تنظیمات فروشگاه',
            'market.checkout-forms.manage' => 'مدیریت فرم‌های تسویه حساب',
            'market.order-statuses.manage' => 'مدیریت وضعیت‌های سفارش',
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
            if ($rname === 'vendor') {
                $role->syncPermissions([
                    'market.dashboard.view',
                    'market.products.view',
                    'market.products.create',
                    'market.products.edit',
                    'market.products.delete',
                    'market.orders.view',
                    'market.warehouses.view',
                ]);
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

        // انتساب نقش فروشنده (vendor) به تمام کاربران ادمین و سوپرادمین موجود در سیستم
        try {
            $adminUsers = \App\Models\User::role(['admin'])->get();
            foreach ($adminUsers as $user) {
                $user->assignRole('vendor');
            }
        } catch (\Throwable $e) {
            Log::warning("Market Installer: Could not assign vendor role to admin users. " . $e->getMessage());
        }

        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles']       = array_values(array_unique($tracker['roles']));
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed default order statuses
        $this->seedDefaultStatuses();

        Log::info("Market Installer: permissions created / roles updated.");
    }

    protected function seedDefaultStatuses(): void
    {
        $statuses = [
            [
                'admin_label' => 'در انتظار پرداخت مشتری',
                'client_label' => 'ثبت اولیه سفارش',
                'color_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'system_type' => 'pending',
                'show_to_client' => true,
                'show_in_client_stepper' => true,
                'show_in_admin_stepper' => true,
                'sort_order' => 10,
            ],
            [
                'admin_label' => 'پرداخت تایید شده / در انتظار بررسی مدیریت',
                'client_label' => 'تایید پرداخت و ثبت نهایی',
                'color_class' => 'bg-blue-50 text-blue-700 border-blue-200',
                'system_type' => 'processing',
                'show_to_client' => true,
                'show_in_client_stepper' => true,
                'show_in_admin_stepper' => true,
                'sort_order' => 20,
            ],
            [
                'admin_label' => 'ارجاع به انبار فروشنده / تامین‌کننده',
                'client_label' => 'در حال آماده‌سازی در انبار',
                'color_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'system_type' => 'processing',
                'show_to_client' => true,
                'show_in_client_stepper' => false,
                'show_in_admin_stepper' => true,
                'sort_order' => 30,
            ],
            [
                'admin_label' => 'تایید موجودی و بسته‌بندی شده',
                'client_label' => 'بسته‌بندی شده و آماده ارسال',
                'color_class' => 'bg-violet-50 text-violet-700 border-violet-200',
                'system_type' => 'processing',
                'show_to_client' => true,
                'show_in_client_stepper' => true,
                'show_in_admin_stepper' => true,
                'sort_order' => 40,
            ],
            [
                'admin_label' => 'تحویل به شرکت توزیع/پیک',
                'client_label' => 'تحویل به مامور ارسال',
                'color_class' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                'system_type' => 'shipped',
                'show_to_client' => true,
                'show_in_client_stepper' => false,
                'show_in_admin_stepper' => true,
                'sort_order' => 50,
            ],
            [
                'admin_label' => 'مرسوله در حال حمل',
                'client_label' => 'مرسوله در حال حمل',
                'color_class' => 'bg-sky-50 text-sky-700 border-sky-200',
                'system_type' => 'shipped',
                'show_to_client' => true,
                'show_in_client_stepper' => true,
                'show_in_admin_stepper' => true,
                'sort_order' => 60,
            ],
            [
                'admin_label' => 'تحویل نهایی به مشتری',
                'client_label' => 'تحویل نهایی شده',
                'color_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'system_type' => 'delivered',
                'show_to_client' => true,
                'show_in_client_stepper' => true,
                'show_in_admin_stepper' => true,
                'sort_order' => 70,
            ],
            [
                'admin_label' => 'سفارش لغو شده',
                'client_label' => 'لغو شده',
                'color_class' => 'bg-rose-50 text-rose-700 border-rose-200',
                'system_type' => 'canceled',
                'show_to_client' => true,
                'show_in_client_stepper' => false,
                'show_in_admin_stepper' => true,
                'sort_order' => 80,
            ],
            [
                'admin_label' => 'مرجوع شده به انبار',
                'client_label' => 'مرجوع شده',
                'color_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                'system_type' => 'returned',
                'show_to_client' => true,
                'show_in_client_stepper' => false,
                'show_in_admin_stepper' => true,
                'sort_order' => 90,
            ],
        ];

        foreach ($statuses as $status) {
            \Modules\Market\App\Models\MarketOrderStatus::updateOrCreate(
                ['admin_label' => $status['admin_label']],
                $status
            );
        }
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
