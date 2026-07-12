<?php

namespace Modules\Tasks\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Modules\BaseModuleInstaller;

class TasksServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Tasks';
    protected string $moduleNameLower = 'tasks';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();

        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        if (class_exists(\Modules\Tasks\Entities\Task::class) && class_exists(\Modules\Tasks\App\Observers\TaskObserver::class)) {
            \Modules\Tasks\Entities\Task::observe(\Modules\Tasks\App\Observers\TaskObserver::class);
        }
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
}
