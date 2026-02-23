<?php

namespace Modules\Booking;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected string $moduleName = 'Booking';

    protected function trackerPath(): string
    {
        return storage_path('app/module-install-trackers/booking.json');
    }

    protected function loadTracker(): array
    {
        $path = $this->trackerPath();
        if (File::exists($path)) {
            return json_decode(File::get($path), true) ?: [];
        }

        return ['permissions' => [], 'roles' => []];
    }

    protected function saveTracker(array $data): void
    {
        $path = $this->trackerPath();
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    public function install(): void
    {
        parent::install();

        // Permission seeding is now handled by BookingPermissionsSeeder.
        // This method is kept for other installation tasks.

        Log::info('Booking Installer: install command executed.');
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();

        parent::uninstall();

        Log::info('Booking Installer: uninstalled and permissions removed.');
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        // This logic is now mostly managed by the seeder's tracker.
        // However, we can keep a simplified version for full cleanup on uninstall.
        $guard   = config('auth.defaults.guard', 'web');
        $seederTrackerPath = storage_path('app/module-install-trackers/booking_perms_seeder.json');

        if (!File::exists($seederTrackerPath)) {
            Log::warning('Booking Installer: Permission seeder tracker not found on uninstall.');
            return;
        }

        $permissions = json_decode(File::get($seederTrackerPath), true) ?: [];

        if (empty($permissions)) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            $perms = Permission::whereIn('name', $permissions)->where('guard_name', $guard)->get();
            foreach ($perms as $perm) {
                $perm->roles()->detach();
                $perm->delete();
            }
            DB::commit();
            Log::info('Booking Installer: Successfully removed module permissions.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Booking Installer: Failed to remove permissions on uninstall: ' . $e->getMessage());
            throw $e;
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        // Clean up trackers
        File::delete($this->trackerPath());
        File::delete($seederTrackerPath);
    }
}
