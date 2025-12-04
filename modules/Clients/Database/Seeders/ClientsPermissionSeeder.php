<?php

namespace Modules\Clients\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClientsPermissionSeeder extends Seeder
{
    public function run()
    {
        // 🔹 پرمیشن‌های پایه + پرمیشن‌های سطح دسترسی به کلاینت‌ها
        $perms = [
            'clients.view',          // دسترسی کلی دیدن ماژول کلاینت‌ها (لیست/نمایش)
            'clients.view.all',      // دیدن همه کلاینت‌ها
            'clients.view.assigned', // دیدن کلاینت‌های خودش + کلاینت‌های assign شده
            'clients.view.own',      // فقط کلاینت‌های ساخته‌شده توسط خودش (اگر جایی خواستی استفاده کنی)
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.manage',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // 🔹 سوپراَدمین: همه‌ی پرمیشن‌های ماژول
        $super = Role::firstOrCreate(['name' => 'super-admin']);
        $super->givePermissionTo($perms);

        // 🔹 ادمین عادی: فعلاً همانند سوپر ادمین برای این ماژول
        // اگر بعداً سیاست‌ات فرق کرد، می‌تونی اینجا محدودترش کنی
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($perms);

        // 🔹 نقش پشتیبانی: دیدن + ایجاد + دیدن کلاینت‌های assign‌شده
        $support = Role::firstOrCreate(['name' => 'support']);
        $support->givePermissionTo([
            'clients.view',
            'clients.view.assigned',
            'clients.create',
        ]);
    }
}
