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

        // --- تعریف مجوزهای کلی مدیریت کاربران و نقش‌ها
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
            // نمونه‌هایی برای کنترل نمایش بخش‌ها (بعداً استفاده می‌کنی)
            'menu.see.users',
            'menu.see.roles',
            // برای بخش‌های دامنه‌ای (در آینده)
            // 'leads.view', 'leads.create', 'leads.update', 'leads.delete',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => $guard]);
        }

        // --- نقش‌ها
        $super = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);
        $admin = Role::firstOrCreate(['name' => 'admin',       'guard_name' => $guard]);
        $sales = Role::firstOrCreate(['name' => 'sales',       'guard_name' => $guard]);
        $support = Role::firstOrCreate(['name' => 'support',   'guard_name' => $guard]);

        // سوپر ادمین همه چیز دارد
        $super->syncPermissions(Permission::pluck('name')->toArray());

        // ادمین بخشی از مجوزها را دارد
        $admin->syncPermissions([
            'users.view','users.create','users.update',
            'roles.view',
            'menu.see.users','menu.see.roles',
        ]);

        // نقش‌های دیگر محدودتر:
        $sales->syncPermissions(['menu.see.users']);
        $support->syncPermissions([]);

        // اگر ایمیل سوپرادمین را از env بدهی، نقش می‌گیرد
        if ($email = env('SUPER_ADMIN_EMAIL')) {
            if ($user = User::where('email', $email)->first()) {
                $user->syncRoles(['super-admin']);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
