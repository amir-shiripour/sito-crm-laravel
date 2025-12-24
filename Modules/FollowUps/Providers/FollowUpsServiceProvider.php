<?php

namespace Modules\FollowUps\Providers;

use App\Support\WidgetRegistry;
use Illuminate\Support\ServiceProvider;

class FollowUpsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'FollowUps';
    protected string $moduleNameLower = 'followups';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();

        $widgetsFile = __DIR__ . '/../config/widgets.php';

        if (file_exists($widgetsFile)) {
            $widgets = require $widgetsFile;

            foreach ($widgets as $key => $definition) {
                WidgetRegistry::register($key, $definition);
            }
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
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }
}
