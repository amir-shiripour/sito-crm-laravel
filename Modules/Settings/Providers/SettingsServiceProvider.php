<?php

namespace Modules\Settings\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SettingsServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\Settings\Http\Controllers';

    public function boot()
    {
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'settings');
    }

    public function register()
    {
        $this->registerHelpers();
    }

    protected function registerRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(__DIR__ . '/../routes/web.php');
    }

    protected function registerHelpers()
    {
        if (file_exists(__DIR__ . '/../Helpers/SettingsHelper.php')) {
            require_once __DIR__ . '/../Helpers/SettingsHelper.php';
        }
    }
}
