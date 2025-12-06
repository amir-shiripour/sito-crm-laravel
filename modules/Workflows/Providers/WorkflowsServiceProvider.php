<?php

namespace Modules\Workflows\Providers;

use Illuminate\Support\ServiceProvider;

class WorkflowsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'workflows');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'workflows');

        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('workflows.php'),
        ], 'workflows-config');
    }
}
