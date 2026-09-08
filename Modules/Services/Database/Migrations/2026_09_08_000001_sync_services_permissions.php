<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Services\Installer;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // حذف مجوزهای منسوخ‌شده مربوط به پروژه‌ها در ماژول سرویس در صورت وجود
        $deprecatedPermissions = [
            'services.projects.view',
            'services.projects.create',
            'services.projects.edit',
            'services.projects.delete',
            'services.projects.manage',
        ];

        $guard = config('auth.defaults.guard', 'web');
        $perms = Permission::whereIn('name', $deprecatedPermissions)->where('guard_name', $guard)->get();
        foreach ($perms as $perm) {
            $perm->roles()->detach();
            $perm->delete();
        }

        // همگام‌سازی کلیه مجوزهای ماژول سرویس
        Installer::syncModulePermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive
    }
};
