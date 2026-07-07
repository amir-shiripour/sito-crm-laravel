<?php

namespace Modules\ContentForge\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ContentForge\App\Providers\ContentForgeServiceProvider as AppServiceProvider;

class ContentForgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AppServiceProvider::class);
    }
}
