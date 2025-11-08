<?php

namespace Modules\Clients\Providers;

use Illuminate\Support\ServiceProvider;

class ClientsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'clients');
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'clients');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
