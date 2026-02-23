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
    protected string $moduleName = 'Sms';
    protected string $moduleNameLower = 'sms';

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
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sms');

        $this->registerTranslations();

        $this->mapRoutes();
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Sms\Console\Commands\ProcessScheduledSms::class,
//                \Modules\Sms\Console\Commands\TestCronSmsCommand::class, // <--- اضافه شد
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
