<?php

namespace Modules\Market\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Market\App\Providers\MarketServiceProvider as AppMarketServiceProvider;
use Modules\Market\App\Providers\RouteServiceProvider;

/**
 * در صورت نیاز به رجیستر شدن از طریق nwidart/laravel-modules
 * این فایل ممکن است مورد نیاز باشد. در غیر این صورت از App\Providers استفاده می‌شود.
 */
class MarketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AppMarketServiceProvider::class);
    }
}
