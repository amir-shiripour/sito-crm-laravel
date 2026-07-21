<?php

declare(strict_types=1);

namespace Modules\SmartBot;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected array $tables = [
        'bot_settings',
        'bot_messages',
        'bot_sessions',
        'bot_answers',
        'bot_questions',
    ];

    protected function trackerPath(): string
    {
        return storage_path('app/module-installer/'.$this->moduleSlug.'/created.json');
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
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

    public function __construct()
    {
        parent::__construct('SmartBot');
    }

    public function install(): void
    {
        parent::install();

        $guard = config('auth.defaults.guard', 'web');

        $perms = [
            'smartbot.view' => 'مشاهده داشبورد دستیار هوشمند',
            'smartbot.manage' => 'مدیریت سوال و جواب دستیار هوشمند',
            'smartbot.settings' => 'مدیریت تنظیمات دستیار هوشمند',
        ];

        $tracker = $this->loadTracker();

        foreach ($perms as $name => $displayName) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                ['display_name' => $displayName]
            );
            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        foreach (['super-admin', 'admin'] as $sysRole) {
            $role = Role::firstOrCreate(['name' => $sysRole, 'guard_name' => $guard]);
            $role->givePermissionTo(array_keys($perms));
        }

        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Log::info("SmartBot Installer: permissions created.");
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();

        try {
            Schema::disableForeignKeyConstraints();
            foreach ($this->tables as $table) {
                Schema::dropIfExists($table);
            }
            Schema::enableForeignKeyConstraints();

            DB::table('migrations')->where('migration', 'like', '%smart_bot%')
                ->orWhere('migration', 'like', '%smartbot%')
                ->delete();
        } catch (\Throwable $e) {
            Log::error("SmartBot Installer DB Cleanup failed: " . $e->getMessage());
        }

        parent::uninstall();
        Log::info("SmartBot Installer: uninstalled and permissions removed.");
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (!$permName) continue;
                $perm = Permission::where('name', $permName)->where('guard_name', $guard)->first();
                if ($perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("SmartBot Installer: removeModuleOwnedPermissionsAndRoles failed: ".$e->getMessage());
            throw $e;
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $path = $this->trackerPath();
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
