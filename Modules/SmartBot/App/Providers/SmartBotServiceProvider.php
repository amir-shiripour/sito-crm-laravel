<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Services\Modules\BaseModuleInstaller;
use Livewire\Livewire;
use Modules\SmartBot\App\Services\Adapters\AiAdapterInterface;
use Modules\SmartBot\App\Services\Adapters\LocalQnAAdapter;

class SmartBotServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'SmartBot';
    protected string $moduleNameLower = 'smartbot';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();

        if (BaseModuleInstaller::isInstalled($this->moduleName)) {
            $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        }

        // Register Livewire Components
        Livewire::component('smartbot.admin.qna-manager', \Modules\SmartBot\App\Livewire\Admin\QnAManager::class);
        Livewire::component('smartbot.admin.analytics-dashboard', \Modules\SmartBot\App\Livewire\Admin\AnalyticsDashboard::class);
        Livewire::component('smartbot.admin.settings-manager', \Modules\SmartBot\App\Livewire\Admin\SettingsManager::class);
        Livewire::component('smartbot.widget.chat-widget', \Modules\SmartBot\App\Livewire\Widget\ChatWidget::class);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Bind Adapter
        $this->app->bind(AiAdapterInterface::class, LocalQnAAdapter::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\App\\Livewire');
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
