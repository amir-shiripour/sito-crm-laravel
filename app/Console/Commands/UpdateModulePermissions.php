<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Facades\Module as NModule;
use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\Log;

class UpdateModulePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:update-permissions {module? : The name of a specific module to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update permissions for active modules without affecting existing user assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $specificModule = $this->argument('module');

        if ($specificModule) {
            $module = NModule::find($specificModule);
            if (!$module) {
                $this->error("Module '{$specificModule}' not found.");
                return Command::FAILURE;
            }
            if (!$module->isEnabled()) {
                $this->error("Module '{$specificModule}' is not enabled.");
                return Command::FAILURE;
            }
            $this->updatePermissions($module->getName());
        } else {
            // Get all enabled physical modules
            $modules = NModule::allEnabled();
            
            if (empty($modules)) {
                $this->info('No enabled modules found.');
                return Command::SUCCESS;
            }

            foreach ($modules as $module) {
                $this->updatePermissions($module->getName());
            }
        }

        $this->info('Permissions updated and cache cleared successfully.');
        return Command::SUCCESS;
    }

    protected function updatePermissions(string $moduleName)
    {
        $this->info("Updating permissions for module: {$moduleName}...");
        
        $installerClass = "\\Modules\\{$moduleName}\\Installer";
        try {
            if (class_exists($installerClass)) {
                $installer = new $installerClass();
                // We call install() because it is written safely using firstOrCreate/givePermissionTo.
                $installer->install();
            } else {
                // For modules without a custom installer class, instantiate BaseModuleInstaller
                $installer = new BaseModuleInstaller($moduleName);
                $installer->install();
            }
            $this->info("✓ Permissions for {$moduleName} updated successfully.");
        } catch (\Throwable $e) {
            $this->error("✗ Failed to update permissions for {$moduleName}: " . $e->getMessage());
            Log::error("UpdateModulePermissions failed for {$moduleName}: " . $e->getMessage());
        }
    }
}
