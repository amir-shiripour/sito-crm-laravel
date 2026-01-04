<?php

namespace Modules\Sms\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapUserRoutes();
        $this->mapApiRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('user')
            ->as('user.')
            ->group(__DIR__ . '/../Routes/web.php');
    }

    protected function mapUserRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('user')
            ->as('user.')
            ->group(__DIR__ . '/../Routes/user.php');
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware(['api'])
            ->prefix('api')
            ->as('api.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
