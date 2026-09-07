<?php

namespace Modules\Services\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ServicesPermissionsSeeder extends Seeder
{
    private array $permissions = [
        // Services (catalog)
        'services.view',
        'services.manage',
        'services.create',
        'services.edit',
        'services.delete',
        'services.duplicate',

        // Invoices
        'services.invoices.view',
        'services.invoices.view.all',
        'services.invoices.create',
        'services.invoices.edit',
        'services.invoices.delete',
        'services.invoices.manage',
        'services.invoices.pay',
        'services.invoices.cancel',
        'services.invoices.cancel-payment',
        'services.invoices.convert',

        // Orders
        'services.orders.view',
        'services.orders.view.all',
        'services.orders.manage',

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
