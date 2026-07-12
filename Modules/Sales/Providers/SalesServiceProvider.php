<?php

namespace Modules\Sales\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class SalesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Sales';
    protected string $moduleNameLower = 'sales';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();

        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        \Livewire\Livewire::component('sales::cockpit-main', \Modules\Sales\App\Livewire\CockpitMain::class);
        \Livewire\Livewire::component('sales::customer-tab', \Modules\Sales\App\Livewire\CustomerTab::class);
        \Livewire\Livewire::component('sales::deal-tab', \Modules\Sales\App\Livewire\DealTab::class);
        \Livewire\Livewire::component('sales::sales-settings', \Modules\Sales\App\Livewire\SalesSettings::class);
        \Livewire\Livewire::component('sales::call-center-tab', \Modules\Sales\App\Livewire\CallCenterTab::class);
        \Livewire\Livewire::component('sales::follow-up-tab', \Modules\Sales\App\Livewire\FollowUpTab::class);
        \Livewire\Livewire::component('sales::task-tab', \Modules\Sales\App\Livewire\TaskTab::class);
        \Livewire\Livewire::component('sales::today-tab', \Modules\Sales\App\Livewire\TodayTab::class);
        \Livewire\Livewire::component('sales::cockpit-goal-manager', \Modules\Sales\App\Livewire\CockpitGoalManager::class);
        \Livewire\Livewire::component('sales::pipeline-kanban', \Modules\Sales\App\Livewire\PipelineKanban::class);
        \Livewire\Livewire::component('sales::deal-360-view', \Modules\Sales\App\Livewire\Deal360View::class);
        \Livewire\Livewire::component('sales::campaign-lead-manager', \Modules\Sales\App\Livewire\CampaignLeadManager::class);
        \Livewire\Livewire::component('sales::sales-report', \Modules\Sales\App\Livewire\SalesReport::class);
        \Livewire\Livewire::component('sales::campaign-contact-manager', \Modules\Sales\App\Livewire\CampaignContactManager::class);
        \Livewire\Livewire::component('sales::campaign-leads-tab', \Modules\Sales\App\Livewire\CampaignLeadsTab::class);

        // ثبت هوک‌های اتوماسیون تماس‌ها برای ساخت خودکار پیگیری‌ها به صورت غیرهمگام (Queue)
        if (class_exists(\Modules\ClientCalls\Entities\ClientCall::class)) {
            \Modules\ClientCalls\Entities\ClientCall::created(function ($clientCall) {
                \Modules\Sales\App\Jobs\CreateFollowupTaskJob::dispatch($clientCall);
            });
        }

        if (class_exists(\Modules\Sales\App\Models\SalesCall::class)) {
            \Modules\Sales\App\Models\SalesCall::created(function ($salesCall) {
                \Modules\Sales\App\Jobs\CreateFollowupTaskJob::dispatch($salesCall);
            });
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

    protected function registerTranslations(): void
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
}
