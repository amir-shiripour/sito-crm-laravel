<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    // ما این را در 'config/fortify.php' تنظیم کردیم، اما اینجا هم آپدیت می‌کنیم
    public const HOME = '/user/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('external-api')
                ->group(base_path('routes/external-api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // این بخش حیاتی برای پنل ادمین است
            // ما فایل روت ادمین را جداگانه با پیشوند 'admin' و میدل‌ورهای 'auth' و 'super_admin' بارگذاری می‌کنیم
            Route::middleware(['web', 'auth', 'super_admin'])
                ->prefix('admin')
                ->name('admin.') // این به روت‌ها پیشوند 'admin.' می‌دهد (مثلاً admin.dashboard)
                ->group(base_path('routes/admin.php'));
        });
    }
}

