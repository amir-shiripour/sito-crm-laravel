<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccountingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'accounting.cheques.view',
            'accounting.cheques.create',
            'accounting.cheques.edit',
            'accounting.cheques.delete',
        ];

        $role = Role::firstOrCreate(['name' => 'super-user']);

        foreach ($permissions as $permission) {
            $p = Permission::firstOrCreate(['name' => $permission]);
            $role->givePermissionTo($p);
        }
    }
}
