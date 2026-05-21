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

        // پیدا کردن نقش super-user
        $superUserRole = Role::where('name', 'super-user')->first();

        // اگر نقش super-user وجود داشت، تمام دسترسی های این ماژول را به آن اختصاص بده
        if ($superUserRole) {
            $superUserRole->givePermissionTo($permissions);
        }
    }
}
