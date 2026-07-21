<?php

namespace Modules\Services\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ServicesPermissionsSeeder extends Seeder
{
    private array $permissions = [
        // Services (catalog)
        'services.view',
        'services.create',
        'services.edit',
        'services.delete',
        'services.duplicate',

        // Projects
        'services.projects.view',
        'services.projects.create',
        'services.projects.manage',
        'services.projects.delete',

        // Invoices
        'services.invoices.view',
        'services.invoices.create',
        'services.invoices.manage',
        'services.invoices.delete',

        // Admin tools
        'status-builder.manage',
        'services.settings.manage',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }
}
