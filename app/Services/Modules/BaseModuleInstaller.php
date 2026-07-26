<?php

namespace App\Services\Modules;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * Check if a module is installed (based on tracker file existence).
     */
    public static function isInstalled(string $moduleName): bool
    {
        $slug = strtolower($moduleName);
        return File::exists(storage_path("app/module-installer/{$slug}/created.json"));
    }

    /**
     * Run an artisan command safely:
     * - checks exit code
     * - logs output
     * - throws exception on failure
     *
     * NOTE: Do NOT blindly inject --force for all commands (some commands don't support it).
     */
    protected function runArtisan(string $command, array $params = [], bool $force = false): void
    {
        // Only add --force when explicitly requested AND the command likely supports it.
        // (module:migrate, module:migrate-refresh, module:migrate-rollback, module:seed, migrate, db:seed, etc.)
        if ($force) {
            // Add --force only if not already present.
            // We do not guarantee the command supports it; caller must set $force only for supported commands.
            $params['--force'] = $params['--force'] ?? true;
        }

        $exitCode = Artisan::call($command, $params);
        $output   = trim(Artisan::output() ?? '');

        Log::info("[ModuleInstaller] {$command} ({$this->moduleName}) exitCode={$exitCode}" . ($output ? " | output: {$output}" : ''));

        if ($exitCode !== 0) {
            throw new \RuntimeException("Artisan command failed: {$command} ({$this->moduleName}). " . ($output ?: 'No output'));
        }
    }

    /**
     * Default install: run module migrations and seeders, then enable module.
     */
    public function install(): void
    {
        try {
            // 0) پاکسازی مایگریشن‌های یتیم یا جداول ناقص که در نصب‌های قبلی مانده‌اند
            $this->cleanOrphanedModuleMigrations();

            // 1) run module migrations first
            $this->runArtisan('module:migrate', [
                'module' => $this->moduleName,
            ], true); // supports --force

            // 2) run module seeders (initial data & permissions)
            $this->runArtisan('module:seed', [
                'module' => $this->moduleName,
            ], true); // supports --force

            // 3) publish assets if needed (module:publish یا custom)

            // 4) finally enable the module so its ServiceProvider & routes are loaded
            // module:enable usually does NOT need --force (and may not support it)
            $this->runArtisan('module:enable', [
                'module' => $this->moduleName,
            ], false);

            // 5) clear caches (optimize:clear does not support --force)
            $this->runArtisan('optimize:clear', [], false);

            Log::info("BaseModuleInstaller: installed {$this->moduleName}");
        } catch (\Throwable $e) {
            Log::error("BaseModuleInstaller install error for {$this->moduleName}: " . $e->getMessage());
            throw $e;
        }
    }

    public function enable(): void
    {
        $this->runArtisan('module:enable', ['module' => $this->moduleName], false);
        $this->runArtisan('optimize:clear', [], false);
        Log::info("BaseModuleInstaller: enabled {$this->moduleName}");
    }

    public function disable(): void
    {
        $this->runArtisan('module:disable', ['module' => $this->moduleName], false);
        $this->runArtisan('optimize:clear', [], false);
        Log::info("BaseModuleInstaller: disabled {$this->moduleName}");
    }

    /**
     * Default reset: create backup of module tables, then migrate-refresh and reseed.
     */
    public function reset(): void
    {
        try {
            // 1) بکاپ از داده‌های ماژول
            $this->backupModuleData();

            // 2) migrate-refresh ماژول
            $this->runArtisan('module:migrate-refresh', [
                'module' => $this->moduleName,
            ], true); // supports --force

            // 3) اجرای seeders ماژول
            $this->runArtisan('module:seed', [
                'module' => $this->moduleName,
            ], true); // supports --force

            // 4) بهینه‌سازی کش
            $this->runArtisan('optimize:clear', [], false);

            Log::info("BaseModuleInstaller: reset {$this->moduleName}");
        } catch (\Throwable $e) {
            Log::error("BaseModuleInstaller reset error for {$this->moduleName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Default uninstall: backup, rollback migrations, remove folder.
     * Concrete modules should override uninstall() if they need custom behavior.
     */
    public function uninstall(): void
    {
        try {
            // create backup before destructive action
            $this->backupModuleData();

            // try to rollback module migrations
            try {
                $this->runArtisan('module:migrate-rollback', [
                    'module' => $this->moduleName,
                ], true); // supports --force
            } catch (\Throwable $e) {
                Log::warning("module:migrate-rollback failed for {$this->moduleName}: " . $e->getMessage());
            }

            // پاکسازی فیزیکی جداول باقی‌مانده و رکورد مایگریشن‌ها
            $this->dropModuleTables();

            // allow module-specific uninstall to drop tables / remove assets
            $this->removeModuleFiles();

            $this->runArtisan('optimize:clear', [], false);

            Log::info("BaseModuleInstaller: uninstalled {$this->moduleName}");
        } catch (\Throwable $e) {
            Log::error("BaseModuleInstaller uninstall error for {$this->moduleName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean up any remaining tables for this module safely
     */
    protected function dropModuleTables(): void
    {
        try {
            $tables = property_exists($this, 'tables') ? $this->tables : [];
            
            Schema::disableForeignKeyConstraints();

            // 1. پاکسازی جداولی که صراحتاً در کلاس Installer تعریف شده‌اند
            foreach ($tables as $table) {
                Schema::dropIfExists($table);
            }

            // 2. پاکسازی تمام جداولی که نامشان با slug ماژول (مثلاً market_) شروع می‌شود
            $allTables = Schema::getTableListing();
            $prefix = $this->moduleSlug . '_';
            foreach ($allTables as $table) {
                if (str_starts_with($table, $prefix)) {
                    Schema::dropIfExists($table);
                }
            }

            Schema::enableForeignKeyConstraints();

            // 3. پاک کردن رکورد مایگریشن‌های مربوطه
            if (Schema::hasTable('migrations')) {
                DB::table('migrations')->where('migration', 'like', '%' . $this->moduleSlug . '%')->delete();
            }
        } catch (\Throwable $e) {
            Log::warning("dropModuleTables failed for {$this->moduleName}: " . $e->getMessage());
        }
    }

    /**
     * Clean up orphaned tables or migration records if a previous installation failed mid-way
     */
    protected function cleanOrphanedModuleMigrations(): void
    {
        try {
            if (!Schema::hasTable('migrations')) {
                return;
            }

            // اگر جداول پایه ماژول وجود نداشته باشند اما مایگریشن‌ها در دیتابیس ثبت شده باشند (حالت یتیم)
            $migratedCount = DB::table('migrations')->where('migration', 'like', '%' . $this->moduleSlug . '%')->count();
            $tables = property_exists($this, 'tables') ? $this->tables : [];
            $existingTablesCount = 0;
            foreach ($tables as $t) {
                if (Schema::hasTable($t)) {
                    $existingTablesCount++;
                }
            }

            // اگر تعداد جداول موجود کمتر از جداول ماژول باشد یا ناهمخوانی وجود داشته باشد، جداول ناقص و رکورد مایگریشن‌ها پاک می‌شوند تا از اول ساخته شوند
            if ($migratedCount > 0 && $existingTablesCount < count($tables)) {
                Log::info("BaseModuleInstaller: Found orphaned/incomplete migration state for {$this->moduleName}. Cleaning up...");
                $this->dropModuleTables();
            }
        } catch (\Throwable $e) {
            Log::warning("cleanOrphanedModuleMigrations failed for {$this->moduleName}: " . $e->getMessage());
        }
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

            // Try both "Modules" and "modules" to be safe on case-sensitive servers
            $tablesFilesCandidates = [
                base_path("Modules/{$this->moduleName}/install-tables.php"),
                base_path("modules/{$this->moduleName}/install-tables.php"),
            ];

            $tables = [];
            foreach ($tablesFilesCandidates as $tablesFile) {
                if (File::exists($tablesFile)) {
                    $tables = include $tablesFile;
                    break;
                }
            }

            // By default try to backup a table named like moduleSlug (e.g., clients)
            if (empty($tables)) {
                $tables = [$this->moduleSlug];
            }

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    $rows = DB::table($table)->get();
                    File::put(
                        $backupDir . '/' . $table . '.json',
                        json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    );
                } else {
                    Log::warning("Backup skipped: table '{$table}' does not exist for module {$this->moduleName}");
                }
            }

            Log::info("Backup created for module {$this->moduleName} at {$backupDir}");
        } catch (\Throwable $e) {
            Log::error("Backup failed for module {$this->moduleName}: " . $e->getMessage());
        }
    }

    protected function removeModuleFiles(): void
    {
        $candidates = [
            base_path("Modules/{$this->moduleName}"),
            base_path("modules/{$this->moduleName}"),
        ];

        foreach ($candidates as $modulePath) {
            if (File::exists($modulePath)) {
                File::deleteDirectory($modulePath);
                Log::info("Module folder removed: {$modulePath}");
                return;
            }
        }

        Log::warning("Module folder not found to remove for {$this->moduleName}");
    }
}
