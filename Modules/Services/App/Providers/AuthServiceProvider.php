<?php

namespace Modules\Services\App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\Order;
use Modules\Services\App\Policies\InvoicePolicy;
use Modules\Services\App\Policies\ServicePolicy;
use Modules\Services\App\Policies\OrderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Invoice::class => InvoicePolicy::class,
        Service::class => ServicePolicy::class,
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
