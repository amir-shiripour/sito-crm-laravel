<?php

namespace Modules\Clients\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Modules\Clients\Entities\ClientSetting;
use App\Services\Modules\BaseModuleInstaller;

class ClientsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'clients');
    }

    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'clients');

        if (BaseModuleInstaller::isInstalled('Clients')) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        }

        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }
        if (file_exists(__DIR__ . '/../Routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        }

        $router->aliasMiddleware('clients.installed.enabled', \Modules\Clients\Middleware\EnsureClientsModuleEnabled::class);

        if (Schema::hasTable('client_settings')) {
            config([
                'clients.labels.singular' => ClientSetting::getValue(
                    'label_singular',
                    config('clients.labels.singular') // مقدار پیش‌فرض از فایل config
                ),
                'clients.labels.plural' => ClientSetting::getValue(
                    'label_plural',
                    config('clients.labels.plural')
                ),
            ]);
        }

        Livewire::component('clients.form-builder', \Modules\Clients\App\Livewire\Settings\ClientFormBuilder::class);
        Livewire::component('clients.username-settings', \Modules\Clients\App\Livewire\Settings\ClientUsernameSettings::class);
        Livewire::component('clients.statuses-builder', \Modules\Clients\App\Livewire\Settings\ClientStatusesManager::class);
        Livewire::component('clients.form', \Modules\Clients\App\Livewire\ClientForm::class);
        Livewire::component('client-auth-settings', \Modules\Clients\App\Livewire\Settings\ClientAuthSettings::class);
    }
}
