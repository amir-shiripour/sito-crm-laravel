<?php

namespace Modules\Clients\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Nwidart\Modules\Facades\Module;

class ClientsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'clients');
    }

    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'clients');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }
        if (file_exists(__DIR__ . '/../Routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        }

        $router->aliasMiddleware('clients.installed.enabled', \Modules\Clients\Middleware\EnsureClientsModuleEnabled::class);
    }
}
