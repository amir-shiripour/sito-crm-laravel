<?php

namespace Modules\Workflows\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Modules\BaseModuleInstaller;
use Modules\Workflows\Console\ProcessWorkflowsCommand;

use Illuminate\Support\Facades\Event;
use Modules\Workflows\Events\NodeReached;
use Modules\Workflows\Listeners\HandleNodeReached;
use Modules\Workflows\Events\WorkflowCompleted;
use Modules\Workflows\Listeners\HandleSubWorkflowCompletion;

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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'workflows');

        $langPath = resource_path('lang/modules/workflows');
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'workflows');
        } else {
            $this->loadTranslationsFrom(module_path('Workflows', 'lang'), 'workflows');
        }

        // Register workflow event listeners
        Event::listen(NodeReached::class, HandleNodeReached::class);
        Event::listen(WorkflowCompleted::class, HandleSubWorkflowCompletion::class);

        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('workflows.php'),
        ], 'workflows-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessWorkflowsCommand::class,
            ]);
        }

        $widgetsFile = __DIR__ . '/../Config/widgets.php';
        if (file_exists($widgetsFile)) {
            $widgets = require $widgetsFile;
            foreach ($widgets as $key => $definition) {
                \App\Support\WidgetRegistry::register($key, $definition);
            }
        }
    }
}
