<?php

namespace Modules\Booking\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BookingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $guard = config('auth.defaults.guard', 'web');

        // The single source of truth for this module's permissions.
        $definedPermissions = [
            'booking.view',
            'booking.manage',

            'booking.settings.manage',

            'booking.categories.view',
            'booking.categories.create',
            'booking.categories.edit',
            'booking.categories.delete',
            'booking.categories.manage',

            'booking.forms.view',
            'booking.forms.create',
            'booking.forms.edit',
            'booking.forms.delete',
            'booking.forms.manage',

            'booking.services.view',
            'booking.services.create',
            'booking.services.edit',
            'booking.services.delete',
            'booking.services.manage',

            'booking.availability.manage',

            'booking.appointments.view',
            'booking.appointments.view.all',
            'booking.appointments.view.own',
            'booking.appointments.create',
            'booking.appointments.edit',
            'booking.appointments.cancel',
            'booking.appointments.manage',

            'booking.reports.view',

            // Statement permissions
            'booking.statement.view',
            'booking.statement.view.all',
            'booking.statement.view.own',
            'booking.statement.create',
            'booking.statement.edit',
            'booking.statement.delete',
            'booking.statement.manage',
        ];

        // Using a simple file-based tracker to detect changes without relying on the installer class
        $trackerPath = storage_path('app/module-install-trackers/booking_perms_seeder.json');
        $trackedPermissions = [];
        if (File::exists($trackerPath)) {
            $trackedPermissions = json_decode(File::get($trackerPath), true) ?: [];
        }

        $permissionsToCreate = array_diff($definedPermissions, $trackedPermissions);
        $permissionsToRemove = array_diff($trackedPermissions, $definedPermissions);

        // Create newly defined permissions
        foreach ($permissionsToCreate as $name) {
            Permission::firstOrCreate([
                'name'       => $name,
                'guard_name' => $guard,
            ]);
        }

        // Remove permissions that are no longer defined
        if (!empty($permissionsToRemove)) {
            DB::beginTransaction();
            try {
                $perms = Permission::whereIn('name', $permissionsToRemove)->where('guard_name', $guard)->get();
                foreach ($perms as $perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('BookingPermissionsSeeder: Failed to remove obsolete permissions: ' . $e->getMessage());
                throw $e;
            }
        }

        // Always grant all defined permissions to system roles
        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo($definedPermissions);
        }

        // Update tracker to match the new state
        File::put($trackerPath, json_encode($definedPermissions, JSON_PRETTY_PRINT));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info('BookingPermissionsSeeder: Permissions have been synced.');
    }
}
