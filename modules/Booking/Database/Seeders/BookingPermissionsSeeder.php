<?php

namespace Modules\Booking\Database\Seeders;

use Illuminate\Database\Seeder;

class BookingPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists('Spatie\\Permission\\Models\\Permission')) {
            $this->command?->warn('Spatie Permission not installed; skipping BookingPermissionsSeeder.');
            return;
        }

        $permissionClass = \Spatie\Permission\Models\Permission::class;
        $roleClass = \Spatie\Permission\Models\Role::class;

        $permissions = [
            'booking.view',
            'booking.manage',
            'booking.settings.manage',

            'booking.services.view',
            'booking.services.create',
            'booking.services.edit',
            'booking.services.delete',
            'booking.services.manage',

            'booking.appointments.view',
            'booking.appointments.create',
            'booking.appointments.edit',

            'booking.categories.view',
            'booking.categories.create',
            'booking.categories.edit',
            'booking.categories.delete',
            'booking.categories.manage',
            'booking.forms.manage',
        ];

        foreach ($permissions as $p) {
            $permissionClass::findOrCreate($p);
        }

        // Optional role assignments (adjust role names to match your project)
        $roles = [
            'SUPER_ADMIN' => $permissions,
            'CRM_ADMIN' => [
                'booking.view',
                'booking.manage',
                'booking.settings.manage',
                'booking.services.view','booking.services.create','booking.services.edit','booking.services.delete','booking.services.manage',
                'booking.appointments.view','booking.appointments.create','booking.appointments.edit',
                'booking.categories.view','booking.categories.create','booking.categories.edit','booking.categories.delete',
                'booking.categories.manage','booking.forms.manage',
            ],
            'OPERATOR' => [
                'booking.view',
                'booking.services.view',
                'booking.appointments.view',
                'booking.appointments.create',
            ],
            'PROVIDER_ROLE' => [
                'booking.view',
                'booking.services.view',
                'booking.services.create',
                'booking.services.edit',
                'booking.appointments.view',
                'booking.categories.view',
                'booking.categories.create',
                'booking.categories.edit',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = $roleClass::findOrCreate($roleName);
            $role->syncPermissions($perms);
        }
    }
}
