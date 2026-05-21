<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\CustomUserField;
use App\View\Composers\ThemeComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        View::composer('auth.register', function ($view) {

            $view->with('allCustomFields', CustomUserField::all()->groupBy('role_name'));

        });
        View::composer(
            [
                'market::web.index',
                'market::web.category',
                'market::web.product.show',
                'layouts.web',
                'themes.market.header',
                'themes.market.footer',
            ],
            ThemeComposer::class
        );
    }
}
