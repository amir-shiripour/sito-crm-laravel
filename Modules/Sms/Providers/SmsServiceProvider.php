<?php

namespace Modules\Sms\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Modules\Sms\Services\SmsManager;
use Modules\Sms\Services\Contracts\SmsSender;
use App\Services\Modules\BaseModuleInstaller;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/sms.php', 'sms');
        $this->mergeConfigFrom(__DIR__.'/../Config/permissions.php', 'permissions.sms');

        $this->app->singleton(SmsManager::class, function ($app) {
            return new SmsManager($app);
        });

        $this->app->alias(SmsManager::class, SmsSender::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (BaseModuleInstaller::isInstalled('Sms')) {
            $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sms');

        $this->mapRoutes();
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Sms\Console\Commands\ProcessScheduledSms::class,
            ]);
        }
    }

    protected function mapRoutes(): void
    {
        if (! $this->app->routesAreCached()) {
            Route::middleware(['web', 'auth'])
                ->prefix('user')
                ->name('user.')
                ->group(__DIR__.'/../Routes/user.php');

            Route::middleware(['api'])
                ->prefix('api')
                ->name('api.')
                ->group(__DIR__.'/../Routes/api.php');
        }
    }
}
