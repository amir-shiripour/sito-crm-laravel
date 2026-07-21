<?php

namespace Modules\Services\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\Project;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Policies\InvoicePolicy;
use Modules\Services\App\Policies\ProjectPolicy;
use Modules\Services\App\Policies\ServicePolicy;
use Modules\Services\App\Services\ProjectService;
use Modules\Services\App\Services\ServiceManagementService;
use Modules\Services\App\Services\StatusBuilderService;
use Illuminate\Support\Facades\Schema;
use Modules\Settings\Entities\Setting;

class ServicesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Services';
    protected string $moduleNameLower = 'services';

    public function boot(): void
    {
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);

        $this->registerViews();
        $this->registerTranslations();
        $this->loadSettingsIntoConfig();
    }

    public function register(): void
    {
        $this->app->bind(ServiceManagementService::class);
        $this->app->bind(StatusBuilderService::class);
        $this->app->bind(ProjectService::class);
    }

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

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php')
        ], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\',
            config('modules.namespace') . '\\' . $this->moduleName . '\\' .
            ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder', ''))
        );
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    private function loadSettingsIntoConfig(): void
    {
        if (Schema::hasTable('settings')) {
            $settings = Setting::where('key', 'like', 'services_%')->get();
            foreach ($settings as $setting) {
                config([$this->moduleNameLower . '.' . $setting->key => $setting->value]);
            }
        }
    }
}
