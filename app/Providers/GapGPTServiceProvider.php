<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GapGPTService;

class GapGPTServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GapGPTService::class, function ($app) {
            return new GapGPTService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
