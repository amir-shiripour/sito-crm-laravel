<?php

namespace Modules\Notifications\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Notifications\Console\Commands\PruneNotificationsCommand;
use Modules\Notifications\Services\NotificationService;

class NotificationsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Notifications';
    protected string $moduleNameLower = 'notifications';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();

        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        if ($this->app->runningInConsole()) {
            $this->commands([
                PruneNotificationsCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(NotificationService::class, function () {
            return new NotificationService();
        });
    }

    protected function registerConfig(): void
    {
        $configFile = module_path($this->moduleName, 'Config/config.php');
        if (file_exists($configFile)) {
            $this->publishes([
                $configFile => config_path($this->moduleNameLower . '.php'),
            ], 'config');

            $this->mergeConfigFrom(
                $configFile,
                $this->moduleNameLower
            );
        }
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
}
