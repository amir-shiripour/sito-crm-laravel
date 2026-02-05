<?php

namespace Modules\Booking\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Booking';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path($this->moduleName, 'Routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(module_path($this->moduleName, 'Routes/api.php'));
    }

    protected function mapAdminRoutes(): void
    {
        // اگر فایل admin.php وجود دارد، آن را لود کن
        if (file_exists(module_path($this->moduleName, 'Routes/admin.php'))) {
            Route::middleware('web')
                ->group(module_path($this->moduleName, 'Routes/admin.php'));
        }
    }
}
