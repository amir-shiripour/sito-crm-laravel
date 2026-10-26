<?php

namespace Modules\FieldService\App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\FieldService\App\Livewire\Admin\BuildingsManager;
use Modules\FieldService\App\Livewire\Admin\CategoriesManager;
use Modules\FieldService\App\Livewire\Admin\FieldServiceSettings;
use Modules\FieldService\App\Livewire\Admin\FleetLiveMap;
use Modules\FieldService\App\Livewire\Admin\OrdersManager;
use Modules\FieldService\App\Livewire\Admin\SettlementsManager;
use Modules\FieldService\App\Livewire\Admin\TechniciansManager;
use Modules\FieldService\App\Livewire\Admin\VendorsManager;
use Modules\FieldService\App\Livewire\B2B\BuildingPassport;
use Modules\FieldService\App\Livewire\B2B\EmergencyDispatch;
use Modules\FieldService\App\Livewire\Customer\CustomerAuthModal;
use Modules\FieldService\App\Livewire\Customer\LiveTracking;
use Modules\FieldService\App\Livewire\Customer\OrderWizard;
use Modules\FieldService\App\Livewire\Technician\DutyToggle;
use Modules\FieldService\App\Livewire\Technician\InvoiceBuilder;
use Modules\FieldService\App\Livewire\Technician\MissionFeed;
use Modules\FieldService\App\Livewire\Technician\TechnicianAuthModal;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Observers\ServiceOrderObserver;
use Modules\FieldService\App\Services\DispatchRadarService;
use Modules\FieldService\App\Services\DynamicRadiusService;
use Modules\FieldService\App\Services\EscrowFinancialService;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;
use Modules\FieldService\App\Services\MapProviderService;
use Modules\FieldService\App\Services\PayaBatchExporterService;

class FieldServiceServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'FieldService';
    protected string $moduleNameLower = 'fieldservice';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->registerLivewireComponents();

        // Register Eloquent Observers for Real-Time & Workflow Sync
        ServiceOrder::observe(ServiceOrderObserver::class);

        // Cascade delete linked Technician when a User is deleted in CRM
        \App\Models\User::deleting(function ($user) {
            \Modules\FieldService\App\Models\Technician::where('user_id', $user->id)->delete();
        });

        // Dynamically share platformName from general system settings across all FieldService views
        \Illuminate\Support\Facades\View::composer('fieldservice::*', function ($view) {
            $view->with('platformName', \Modules\FieldService\App\Models\FieldServiceSetting::getPlatformName());
        });
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(DispatchRadarService::class);
        $this->app->singleton(DynamicRadiusService::class);
        $this->app->singleton(EscrowFinancialService::class);
        $this->app->singleton(FieldServiceWorkflowBridgeService::class);
        $this->app->singleton(MapProviderService::class);
        $this->app->singleton(PayaBatchExporterService::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    public function registerTranslations(): void
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

    protected function registerLivewireComponents(): void
    {
        Livewire::component('fieldservice::fleet-live-map', FleetLiveMap::class);
        Livewire::component('fieldservice::orders-manager', OrdersManager::class);
        Livewire::component('fieldservice::technicians-manager', TechniciansManager::class);
        Livewire::component('fieldservice::categories-manager', CategoriesManager::class);
        Livewire::component('fieldservice::buildings-manager', BuildingsManager::class);
        Livewire::component('fieldservice::vendors-manager', VendorsManager::class);
        Livewire::component('fieldservice::settlements-manager', SettlementsManager::class);
        Livewire::component('fieldservice::settings', FieldServiceSettings::class);

        // Portals Livewire components
        Livewire::component('fieldservice::customer-auth-modal', CustomerAuthModal::class);
        Livewire::component('fieldservice::technician-auth-modal', TechnicianAuthModal::class);
        Livewire::component('fieldservice::order-wizard', OrderWizard::class);
        Livewire::component('fieldservice::live-tracking', LiveTracking::class);
        Livewire::component('fieldservice::technician-duty-toggle', DutyToggle::class);
        Livewire::component('fieldservice::mission-feed', MissionFeed::class);
        Livewire::component('fieldservice::invoice-builder', InvoiceBuilder::class);
        Livewire::component('fieldservice::building-passport', BuildingPassport::class);
        Livewire::component('fieldservice::emergency-dispatch', EmergencyDispatch::class);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
