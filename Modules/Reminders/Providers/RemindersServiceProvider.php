<?php

namespace Modules\Reminders\Providers;

use App\Support\WidgetRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\Reminders\Console\ProcessRemindersCommand; // <--- اضافه شده

class RemindersServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Reminders';
    protected string $moduleNameLower = 'reminders';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $widgetsFile = __DIR__ . '/../config/widgets.php';

        if (file_exists($widgetsFile)) {
            $widgets = require $widgetsFile;

            foreach ($widgets as $key => $definition) {
                WidgetRegistry::register($key, $definition);
            }
        }

        // <--- اضافه شده: ثبت دستورات کنسول
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessRemindersCommand::class,
            ]);
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
