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
        // load views (namespace: clients::)
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'clients');

        // load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // register routes - admin & user routes inside module
        // nWidart لود سرویس پروایدر را فقط زمانی انجام می‌دهد که ماژول enabled باشد.
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // register module specific middleware alias (if you want to use alias in route groups)
        $router->aliasMiddleware('clients.installed.enabled', \Modules\Clients\Middleware\EnsureClientsModuleEnabled::class);
    }
}
