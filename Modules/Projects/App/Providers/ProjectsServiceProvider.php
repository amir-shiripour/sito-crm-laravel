<?php

namespace Modules\Projects\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectTask;
use Modules\Projects\App\Observers\ProjectObserver;
use Modules\Projects\App\Observers\ProjectTaskObserver;
use Modules\Projects\App\Policies\ProjectPolicy;
use Modules\Projects\App\Services\ProjectsService;
use Modules\Projects\App\Services\ProjectStatusBuilderService;

class ProjectsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Projects';
    protected string $moduleNameLower = 'projects';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);

        Project::observe(ProjectObserver::class);
        ProjectTask::observe(ProjectTaskObserver::class);

        $this->registerViews();
        $this->registerTranslations();
        $this->registerConfig();

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Projects\App\Console\CheckOverdueTasksCommand::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
                $schedule->command('projects:check-overdue-tasks')->everyMinute();
            });
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(ProjectsService::class);
        $this->app->bind(ProjectStatusBuilderService::class);
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php')
        ], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace(
            '/',
            '\\',
            config('modules.namespace') . '\\' . $this->moduleName . '\\' .
            ltrim(config('modules.paths.generator.component-class.path', 'App/View/Components'), config('modules.paths.app_folder', ''))
        );
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    public function provides(): array
    {
        return [
            ProjectsService::class,
            ProjectStatusBuilderService::class,
        ];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths', []) as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}
