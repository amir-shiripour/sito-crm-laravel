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
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // نقش‌ها
        $super   = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);
        $admin   = Role::firstOrCreate(['name' => 'admin',       'guard_name' => $guard]);
        $sales   = Role::firstOrCreate(['name' => 'sales',       'guard_name' => $guard]);
        $support = Role::firstOrCreate(['name' => 'support',     'guard_name' => $guard]);

        // سوپرادمین: همه‌چیز
        $super->syncPermissions(Permission::pluck('name')->toArray());

        // ادمین: مثل قبل، بدون دسترسی به مدیریت فیلدها (فقط سوپر)
        $admin->syncPermissions([
            'users.view','users.create','users.update',
            'roles.view',
            'menu.see.users','menu.see.roles',
            // عمداً: بدون menu.see.custom-fields و بدون custom-fields.*
        ]);

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
