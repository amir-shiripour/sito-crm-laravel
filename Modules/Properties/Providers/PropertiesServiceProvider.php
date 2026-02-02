<?php

namespace Modules\Properties\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use App\Services\Modules\BaseModuleInstaller;

class PropertiesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'properties');
    }

    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'properties');

        if (BaseModuleInstaller::isInstalled('Properties')) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        }

        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }
        if (file_exists(__DIR__ . '/../Routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        }

        Livewire::component('properties.statuses-manager', \Modules\Properties\App\Livewire\Settings\PropertyStatusesManager::class);
    }
}
