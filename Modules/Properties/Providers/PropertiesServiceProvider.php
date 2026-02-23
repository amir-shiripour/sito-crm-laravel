<?php

namespace Modules\Properties\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use App\Services\Modules\BaseModuleInstaller;

class PropertiesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Properties';
    protected string $moduleNameLower = 'properties';

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'properties');
    }

    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'properties');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->registerTranslations();

        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }
        if (file_exists(__DIR__ . '/../Routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        }

        Livewire::component('properties.statuses-manager', \Modules\Properties\App\Livewire\Settings\PropertyStatusesManager::class);
        Livewire::component('properties.settings.csv-importer', \Modules\Properties\App\Livewire\Settings\CsvImporter::class);
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
