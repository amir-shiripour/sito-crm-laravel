<?php

namespace Modules\Services\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Services\Installer;

class ServicesUpdatePermissions extends Command
{
    protected $signature = 'services:update-permissions';
    protected $description = 'Update Services module permissions without reinstalling everything.';

    public function handle(): int
    {
        $this->info('Updating Services module permissions...');

        Installer::syncModulePermissions();

        $this->info('Services module permissions updated successfully.');
        return self::SUCCESS;
    }
}
