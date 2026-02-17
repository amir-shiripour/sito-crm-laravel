<?php

namespace Modules\Booking\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Modules\BaseModuleInstaller;
use Modules\Booking\Entities\BookingSetting;
use Illuminate\Support\Facades\Schema;

class BookingServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Booking';
    protected string $moduleNameLower = 'booking';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();

        // Load migrations unconditionally
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        // Load dynamic labels from DB if table exists - REMOVED BaseModuleInstaller check
        if (Schema::hasTable('booking_settings')) {
            try {
                $labelProvider = BookingSetting::getValue('label_provider');
                $labelProviders = BookingSetting::getValue('label_providers');

                if ($labelProvider) {
                    config(['booking.labels.provider' => $labelProvider]);
                }
                if ($labelProviders) {
                    config(['booking.labels.providers' => $labelProviders]);
                }
            } catch (\Exception $e) {
                // Ignore errors during boot
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Booking\Console\Commands\BookingInstall::class,
                \Modules\Booking\Console\Commands\BookingUpdatePermissions::class,
                \Modules\Booking\Console\Commands\BookingCleanupHolds::class,
                \Modules\Booking\Console\Commands\BookingHandlePaymentTimeouts::class,
                \Modules\Booking\Console\Commands\BookingDispatchReminders::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerViews(): void
    {
        $viewPath   = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }
}
