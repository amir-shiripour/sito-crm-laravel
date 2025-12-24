<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // پایه: کاربران/نقش‌ها + منوها
        $permissions = [
            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-roles',

            // Roles
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.assign-permissions',

            // Menus
            'menu.see.users',
            'menu.see.roles',

            // Custom Fields (جدید)
            'menu.see.custom-fields',            // نمایش آیتم منو
            'custom-fields.view',
            'custom-fields.create',
            'custom-fields.update',
            'custom-fields.delete',

            'modules.manage'
        ];

        // ایجاد مجوزها
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // نقش‌ها
        $super   = Role::firstOrCreate([
            'name'         => 'super-admin',
            'guard_name'   => $guard,
            'display_name' => 'مدیر ارشد'  // نام فارسی نقش
        ]);

        $admin   = Role::firstOrCreate([
            'name'         => 'admin',
            'guard_name'   => $guard,
            'display_name' => 'مدیر'       // نام فارسی نقش
        ]);

        $sales   = Role::firstOrCreate([
            'name'         => 'sales',
            'guard_name'   => $guard,
            'display_name' => 'فروش'       // نام فارسی نقش
        ]);

        $support = Role::firstOrCreate([
            'name'         => 'support',
            'guard_name'   => $guard,
            'display_name' => 'پشتیبانی'  // نام فارسی نقش
        ]);

        // سوپرادمین: همه‌چیز
        $super->syncPermissions(Permission::pluck('name')->toArray());

        // ادمین: همه permission ها به جز موارد استثنا
        // استثناها:
        // - modules.manage (مدیریت ماژول‌ها - نصب/حذف/فعال/غیرفعال)
        // - clients.manage (تنظیمات لیبل و سایر تنظیمات ماژول clients)
        $allPermissions = Permission::pluck('name')->toArray();
        $excludedPermissions = [
            'modules.manage',
            'clients.manage',
        ];

        $adminPermissions = array_filter($allPermissions, function ($perm) use ($excludedPermissions) {
            return !in_array($perm, $excludedPermissions);
        });

        $admin->syncPermissions($adminPermissions);

        // سایر نقش‌ها
        $sales->syncPermissions(['menu.see.users']);
        $support->syncPermissions([]);

        // انتساب نقش سوپرادمین به کاربر env
        if ($email = env('SUPER_ADMIN_EMAIL')) {
            if ($user = User::where('email', $email)->first()) {
                $user->syncRoles(['super-admin']);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
