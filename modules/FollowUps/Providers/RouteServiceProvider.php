<?php

namespace Modules\FollowUps\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\\FollowUps\\Http\\Controllers';

    public function map(): void
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->namespace($this->moduleNamespace)
            ->group(module_path('FollowUps', '/Routes/web.php'));
    }
}
