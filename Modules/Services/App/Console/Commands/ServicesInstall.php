<?php

namespace Modules\Services\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Services\Installer;

class ServicesInstall extends Command
{
    protected $signature = 'services:install';
    protected $description = 'Install Services module permissions/roles (does not run migrations automatically).';

    public function handle(): int
    {
        $installer = app(Installer::class);
        $installer->install();

        $this->info('Services module installed (permissions created).');
        $this->line('Now run: php artisan migrate');
        return self::SUCCESS;
    }
}
