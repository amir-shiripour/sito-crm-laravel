<?php

namespace Modules\ContentForge\App\Providers;

use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentCategory;
use Modules\ContentForge\App\Observers\ContentPostObserver;
use Modules\ContentForge\App\Observers\ContentCategoryObserver;
use Modules\ContentForge\App\Livewire\Admin\PostEditor;
use Modules\ContentForge\App\Livewire\Admin\PostList;
use Modules\ContentForge\App\Livewire\Admin\CategoryManager;
use Modules\ContentForge\App\Livewire\Admin\TagManager;
use Modules\ContentForge\App\Livewire\Admin\EntityManager;
use Modules\ContentForge\App\Livewire\Admin\CommentManager;
use Modules\ContentForge\App\Livewire\Admin\ShortLinkManager;
use Modules\ContentForge\App\Livewire\Admin\RedirectManager;
use Modules\ContentForge\App\Livewire\Admin\ContentSettings;
use Modules\ContentForge\App\Livewire\Web\CommentForm;
use Modules\ContentForge\App\Livewire\Web\CommentList;
use App\Services\Modules\BaseModuleInstaller;

class ContentForgeServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ContentForge';
    protected string $moduleNameLower = 'contentforge';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();

        if (BaseModuleInstaller::isInstalled($this->moduleName)) {
            $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
            
            // Register Observers
            ContentPost::observe(ContentPostObserver::class);
            ContentCategory::observe(ContentCategoryObserver::class);
        }

        // Register Livewire Components
        Livewire::component('contentforge::admin.post-editor', PostEditor::class);
        Livewire::component('contentforge::admin.post-list', PostList::class);
        Livewire::component('contentforge::admin.category-manager', CategoryManager::class);
        Livewire::component('contentforge::admin.tag-manager', TagManager::class);
        Livewire::component('contentforge::admin.entity-manager', EntityManager::class);
        Livewire::component('contentforge::admin.comment-manager', CommentManager::class);
        Livewire::component('contentforge::admin.short-link-manager', ShortLinkManager::class);
        Livewire::component('contentforge::admin.redirect-manager', RedirectManager::class);
        Livewire::component('contentforge::admin.content-settings', ContentSettings::class);
        Livewire::component('contentforge::web.comment-form', CommentForm::class);
        Livewire::component('contentforge::web.comment-list', CommentList::class);

        // Dynamically add ContentForge upload folders to optimizer directories list in config
        $this->registerOptimizerDirectories();
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerOptimizerDirectories(): void
    {
        try {
            if (BaseModuleInstaller::isInstalled($this->moduleName)) {
                $entityIds = \Modules\ContentForge\App\Models\ContentEntity::pluck('id')->toArray();
                $dirs = [];
                foreach ($entityIds as $id) {
                    $dirs[] = "content/{$id}/covers";
                    $dirs[] = "content/{$id}/gallery";
                    $dirs[] = "content/{$id}/og";
                }

                // Add to config array
                $existing = config('image_optimizer.directories', []);
                config(['image_optimizer.directories' => array_unique(array_merge($existing, $dirs))]);
            }
        } catch (\Throwable $e) {
            // Ignore during boot or if migration not run yet
        }
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

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
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.ltrim(config('modules.paths.generator.component-class.path', 'App/View/Components'), config('modules.paths.app_folder','')));
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
