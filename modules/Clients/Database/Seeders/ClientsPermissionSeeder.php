<?php

namespace Modules\Clients\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClientsPermissionSeeder extends Seeder
{
    public function run()
    {
        $perms = [
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.manage',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($perms);

        $support = Role::firstOrCreate(['name' => 'support']);
        $support->givePermissionTo(['clients.view', 'clients.create']);
    }
}
