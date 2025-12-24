<?php

namespace Modules\Booking\Console\Commands;

use Illuminate\Console\Command;
use Modules\Booking\Installer;

class BookingInstall extends Command
{
    protected $signature = 'booking:install';
    protected $description = 'Install Booking module permissions/roles (does not run migrations automatically).';

    public function handle(): int
    {
        $installer = app(Installer::class);
        $installer->install();

        $this->info('Booking module installed (permissions created).');
        $this->line('Now run: php artisan migrate');
        return self::SUCCESS;
    }
}
