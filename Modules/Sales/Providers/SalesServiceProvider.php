<?php

namespace Modules\Sales\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class SalesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Sales';
    protected string $moduleNameLower = 'sales';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();

        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        \Livewire\Livewire::component('sales::cockpit-main', \Modules\Sales\App\Livewire\CockpitMain::class);
        \Livewire\Livewire::component('sales::customer-tab', \Modules\Sales\App\Livewire\CustomerTab::class);
        \Livewire\Livewire::component('sales::call-center-tab', \Modules\Sales\App\Livewire\CallCenterTab::class);
        \Livewire\Livewire::component('sales::follow-up-tab', \Modules\Sales\App\Livewire\FollowUpTab::class);
        \Livewire\Livewire::component('sales::task-tab', \Modules\Sales\App\Livewire\TaskTab::class);
        \Livewire\Livewire::component('sales::today-tab', \Modules\Sales\App\Livewire\TodayTab::class);
        \Livewire\Livewire::component('sales::cockpit-goal-manager', \Modules\Sales\App\Livewire\CockpitGoalManager::class);
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
