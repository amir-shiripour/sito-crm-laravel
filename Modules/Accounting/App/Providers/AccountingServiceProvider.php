<?php

namespace Modules\Accounting\App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Accounting\App\Livewire\AccountingSettings;
use Modules\Accounting\App\Livewire\ExpenseCreate;
use Modules\Accounting\App\Livewire\InvoiceCreate;
use Modules\Accounting\App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Accounting\App\Events\PaymentRecorded;
use Modules\Accounting\App\Listeners\RecordPaymentInAccounting;
use Modules\Wallet\App\Events\WalletTransactionCreated;
use Modules\Accounting\App\Listeners\CrossModuleTransactionListener;

class AccountingServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Accounting';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'accounting';

    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        PaymentRecorded::class => [
            RecordPaymentInAccounting::class,
        ],
        WalletTransactionCreated::class => [
            [CrossModuleTransactionListener::class, 'handleWalletTransactionCreated'],
        ],
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Grant all permissions to super-admin
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        $this->registerConfig();
        $this->registerTranslations();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);

        // Register Livewire components
        Livewire::component('accounting::accounting-settings', AccountingSettings::class);
        Livewire::component('accounting::invoice-create', InvoiceCreate::class);
        Livewire::component('accounting::expense-create', ExpenseCreate::class);

        // Register events and listeners
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower
        );

        // Merge permissions config
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/permissions.php'), 'permissions'
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'resources/lang'));
        }
    }
}
