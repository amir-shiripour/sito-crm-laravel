<?php

namespace Modules\ContractForge\Providers;

use Illuminate\Support\ServiceProvider;

class ContractForgeServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ContractForge';
    protected string $moduleNameLower = 'contractforge';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();

        // Load migrations
        $this->loadMigrationsFrom(base_path('Modules/ContractForge/Database/Migrations'));

        // Load routes
        $this->loadRoutesFrom(base_path('Modules/ContractForge/Routes/web.php'));
    }

    public function register(): void
    {
        //
    }

    protected function registerConfig(): void
    {
        // Fallback or empty config
        $this->publishes([
            base_path('Modules/ContractForge/Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            base_path('Modules/ContractForge/Config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = base_path('Modules/ContractForge/resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    protected function registerTranslations(): void
    {
        $langPath = base_path('Modules/ContractForge/lang');
        $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
    }
}
