<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تعریف دسترسی ها
        $permissions = [
            'accounting.invoices.view',
            'accounting.transactions.view',
            'accounting.banks.view',
            'accounting.settings.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // پیدا کردن نقش super-admin
        $superUserRole = Role::where('name', 'super-admin')->first();

        // اگر نقش super-admin وجود داشت، تمام دسترسی های این ماژول را به آن اختصاص بده
        if ($superUserRole) {
            $superUserRole->givePermissionTo($permissions);
        }
    }
}
