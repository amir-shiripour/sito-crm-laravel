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
        $this->registerTranslations();

        \Livewire\Livewire::component('booking.user.schedule-manager', \Modules\Booking\App\Livewire\User\ScheduleManager::class);
        \Livewire\Livewire::component('booking.user.booking-waitlist-manager', \Modules\Booking\App\Livewire\User\BookingWaitlistManager::class);
        \Livewire\Livewire::component('booking.user.booking-waitlist-modal', \Modules\Booking\App\Livewire\User\BookingWaitlistModal::class);
        \Livewire\Livewire::component('booking.user.clinic-live-monitoring', \Modules\Booking\App\Livewire\User\ClinicLiveMonitoring::class);
        \Livewire\Livewire::component('modules.booking.app.livewire.user.clinic-live-monitoring', \Modules\Booking\App\Livewire\User\ClinicLiveMonitoring::class);

        if (class_exists(\Modules\Booking\App\Models\TreatmentPlan::class)) {
            \Modules\Booking\App\Models\TreatmentPlan::observe(\Modules\Booking\App\Observers\TreatmentPlanObserver::class);
        }

        \Modules\Booking\Entities\BookingPayment::observe(\Modules\Booking\App\Observers\BookingPaymentObserver::class);
        \Modules\Booking\Entities\Appointment::observe(\Modules\Booking\App\Observers\AppointmentObserver::class);

        // Load migrations unconditionally
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        try {
            // Load dynamic labels from DB if table exists - REMOVED BaseModuleInstaller check
            if (Schema::hasTable('booking_settings')) {
                $labelProvider = BookingSetting::getValue('label_provider');
                $labelProviders = BookingSetting::getValue('label_providers');
                $labelService = BookingSetting::getValue('label_service');
                $labelServices = BookingSetting::getValue('label_services');

                if ($labelProvider) {
                    config(['booking.labels.provider' => $labelProvider]);
                }
                if ($labelProviders) {
                    config(['booking.labels.providers' => $labelProviders]);
                }
                if ($labelService) {
                    config(['booking.labels.service' => $labelService]);
                }
                if ($labelServices) {
                    config(['booking.labels.services' => $labelServices]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore errors during boot or if database is not yet set up
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
        $this->app->register(\Modules\Booking\App\Providers\DoctorProfileServiceProvider::class);
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

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }
}
