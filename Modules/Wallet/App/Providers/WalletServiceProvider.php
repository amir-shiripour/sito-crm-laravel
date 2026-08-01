<?php

namespace Modules\Wallet\App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Wallet\App\Services\WalletService;

class WalletServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Wallet';
    protected string $moduleNameLower = 'wallet';

    public function register(): void
    {
        $this->app->singleton(WalletService::class, function ($app) {
            return new WalletService();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'wallet');
        $this->registerTranslations();
        $this->mapRoutes();
    }

    protected function mapRoutes(): void
    {
        if (! $this->app->routesAreCached()) {
            Route::middleware(['web', 'auth'])
                ->prefix('user')
                ->name('user.')
                ->group(__DIR__.'/../../Routes/web.php');

            Route::middleware(['api'])
                ->prefix('api')
                ->name('api.')
                ->group(__DIR__.'/../../Routes/api.php');
        }
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
