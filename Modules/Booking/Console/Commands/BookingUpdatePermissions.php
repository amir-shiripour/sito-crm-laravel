<?php

namespace Modules\Booking\Console\Commands;

use Illuminate\Console\Command;
use Modules\Booking\Installer;

class BookingUpdatePermissions extends Command
{
    protected $signature = 'booking:update-permissions';
    protected $description = 'Update Booking module permissions without reinstalling everything.';

    public function handle(): int
    {
        $this->info('Updating Booking module permissions...');

        // The Installer::install() method uses firstOrCreate, so it's safe to run multiple times.
        // It will add new permissions without deleting existing ones or their assignments.
        $installer = app(Installer::class);
        $installer->install();

        $this->info('Booking module permissions updated successfully.');
        return self::SUCCESS;
    }
}
