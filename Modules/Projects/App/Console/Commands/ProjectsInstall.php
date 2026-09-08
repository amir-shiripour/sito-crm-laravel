<?php

namespace Modules\Projects\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Projects\Installer;

class ProjectsInstall extends Command
{
    protected $signature = 'projects:install';
    protected $description = 'Install Projects module permissions/roles (does not run migrations automatically).';

    public function handle(): int
    {
        $installer = app(Installer::class);
        $installer->install();

        $this->info('Projects module installed (permissions created).');
        $this->line('Now run: php artisan migrate');
        return self::SUCCESS;
    }
}
