<?php

namespace App\Services\Modules;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BaseModuleInstaller implements ModuleInstallerInterface
{
    protected string $moduleName; // e.g. 'Clients'
    protected string $moduleSlug; // e.g. 'clients'

    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
        $this->moduleSlug = strtolower($moduleName);
    }

    /**
     * Default install: enable package, run module migrations and seeders.
     */
    public function install(): void
    {
        try {
            // 1) run module migrations first
            Artisan::call('module:migrate', ['module' => $this->moduleName]);

            // 2) run module seeders (initial data & permissions)
            Artisan::call('module:seed', ['module' => $this->moduleName]);

            // 3) publish assets if needed (module:publish یا custom)

            // 4) finally enable the module so its ServiceProvider & routes are loaded
            Artisan::call('module:enable', ['module' => $this->moduleName]);

            Artisan::call('optimize:clear');

            Log::info("BaseModuleInstaller: installed {$this->moduleName}");
        } catch (\Throwable $e) {
            Log::error("BaseModuleInstaller install error for {$this->moduleName}: " . $e->getMessage());
            throw $e;
        }
    }

    public function enable(): void
    {
        Artisan::call('module:enable', ['module' => $this->moduleName]);
        Artisan::call('optimize:clear');
        Log::info("BaseModuleInstaller: enabled {$this->moduleName}");
    }

    public function disable(): void
    {
        Artisan::call('module:disable', ['module' => $this->moduleName]);
        Artisan::call('optimize:clear');
        Log::info("BaseModuleInstaller: disabled {$this->moduleName}");
    }

    /**
     * Default reset: create backup of module tables, then truncate known tables and reseed.
     * Concrete modules should override truncateModuleTables() to list their tables.
     */
    public function reset(): void
    {
        try {
            $this->backupModuleData();

            $this->truncateModuleTables();

            // reseed
            Artisan::call('module:seed', ['module' => $this->moduleName]);
            Artisan::call('optimize:clear');

            Log::info("BaseModuleInstaller: reset {$this->moduleName}");
        } catch (\Throwable $e) {
            Log::error("BaseModuleInstaller reset error for {$this->moduleName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Default uninstall: rollback module migrations if possible, drop tables if module overrides, and remove folder.
     * Concrete modules should override uninstall() if they need custom behavior.
     */
    public function uninstall(): void
    {
        try {
            // create backup before destructive action
            $this->backupModuleData();

            // try to rollback module migrations
            try {
                Artisan::call('module:migrate-rollback', ['module' => $this->moduleName]);
            } catch (\Throwable $e) {
                // rollback can fail due to FK or partial migrations — log and continue to module-specific uninstall
                Log::warning("module:migrate-rollback failed for {$this->moduleName}: " . $e->getMessage());
            }

            // allow module-specific uninstall to drop tables / remove assets
            $this->removeModuleFiles();

            Artisan::call('optimize:clear');

            Log::info("BaseModuleInstaller: uninstalled {$this->moduleName}");
        } catch (\Throwable $e) {
            Log::error("BaseModuleInstaller uninstall error for {$this->moduleName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Default: no-op. Modules should override to truncate their tables.
     */
    protected function truncateModuleTables(): void
    {
        // default does nothing — concrete modules implement this
    }

    /**
     * Backup module-related tables data to storage (JSON) before destructive operations.
     * This is a safe default backup (not SQL dump).
     */
    protected function backupModuleData(): void
    {
        try {
            $backupDir = storage_path('app/module-backups/' . $this->moduleSlug . '/' . Carbon::now()->format('Ymd_His'));
            if (! File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            // If module exposes a file with list of tables (convention modules/<Name>/install-tables.php), use it
            $tablesFile = base_path("modules/{$this->moduleName}/install-tables.php");
            $tables = [];
            if (File::exists($tablesFile)) {
                $tables = include $tablesFile;
            }

            // By default try to backup a table named like moduleSlug (e.g., clients)
            if (empty($tables)) {
                $tables = [$this->moduleSlug];
            }

            foreach ($tables as $table) {
                if (\Schema::hasTable($table)) {
                    $rows = DB::table($table)->get();
                    File::put($backupDir . '/' . $table . '.json', json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }

            Log::info("Backup created for module {$this->moduleName} at {$backupDir}");
        } catch (\Throwable $e) {
            Log::error("Backup failed for module {$this->moduleName}: " . $e->getMessage());
            // do not block uninstall/reset if backup fails, but raise warning via logs
        }
    }

    protected function removeModuleFiles(): void
    {
        $modulePath = base_path("modules/{$this->moduleName}");
        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
        }
    }
}
