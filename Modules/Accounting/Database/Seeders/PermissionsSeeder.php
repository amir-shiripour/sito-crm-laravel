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
        $permissions = [
            'accounting.invoices.view',
            'accounting.transactions.view',
            'accounting.banks.view',
            'accounting.settings.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (['super-admin', 'admin'] as $roleName) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['display_name' => $roleName === 'super-admin' ? 'مدیر کل' : 'مدیر']
            );
            $role->givePermissionTo($permissions);
        }
    }
}
