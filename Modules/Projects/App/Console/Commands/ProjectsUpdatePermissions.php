<?php

namespace Modules\Projects\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Projects\Installer;

class ProjectsUpdatePermissions extends Command
{
    protected $signature = 'projects:update-permissions';
    protected $description = 'Update Projects module permissions without reinstalling everything.';

    public function handle(): int
    {
        $this->info('Updating Projects module permissions...');

        Installer::syncModulePermissions();

        $this->info('Projects module permissions updated successfully.');
        return self::SUCCESS;
    }
}
