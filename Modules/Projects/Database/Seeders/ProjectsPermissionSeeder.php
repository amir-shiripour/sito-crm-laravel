<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ProjectsPermissionSeeder extends Seeder
{
    private array $permissions = [
        'projects.view',
        'projects.create',
        'projects.edit',
        'projects.delete',
        'projects.cancel',
        'projects.manage',
        'projects.templates.view',
        'projects.templates.create',
        'projects.templates.edit',
        'projects.templates.delete',
        'projects.templates.manage',
        'projects.categories.manage',
        'projects.status-builder.manage',
        'projects.settings.manage',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }
}
