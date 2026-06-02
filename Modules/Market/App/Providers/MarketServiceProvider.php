<?php

namespace Modules\Market\App\Providers;

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Support\WidgetRegistry;
use App\Services\Modules\BaseModuleInstaller;
use Modules\Market\App\Livewire\Admin\VendorForm;
use Modules\Market\App\Livewire\Vendor\ProductForm;
use Modules\Market\App\Livewire\Vendor\KycWizard;
use Modules\Market\App\Livewire\Vendor\ProductManager;
use Modules\Market\App\Livewire\Vendor\VendorWarehouseManager;
use Modules\Market\App\Livewire\Admin\MarketSettings;
use Modules\Market\App\Livewire\Admin\MasterProductForm;
use Modules\Market\App\Livewire\Admin\BrandManager;
use Modules\Market\App\Livewire\Admin\CategoryManager;
use Modules\Market\App\Livewire\Admin\AttributeManager;
use Modules\Market\App\Livewire\Admin\VendorProductReview;
use Modules\Market\App\Livewire\Admin\WarehouseManager;
use Modules\Market\App\Livewire\Admin\WarehouseStockController;
use Modules\Market\App\Livewire\Admin\ProductVariantSelector;
use Modules\Market\App\Livewire\Admin\CheckoutFormManager;
use Modules\Market\App\Livewire\Web\CartManager;
use Modules\Market\App\Livewire\Web\PopupCart;
use Modules\Market\App\Livewire\Web\CartCounter;
use Modules\Market\App\Livewire\Web\AddToCartButton;
use Modules\Market\App\Livewire\web\CheckoutModal;
use Modules\Market\App\Livewire\web\CheckoutPage;
use Modules\Market\App\Livewire\Web\LocationModal;
use Modules\Market\App\Livewire\user\OrderForm;
use Modules\Market\App\Observers\VendorObserver;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\Warehouse;

class MarketServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Market';

    protected string $moduleNameLower = 'market';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();

        if (BaseModuleInstaller::isInstalled($this->moduleName)) {
            $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
            $this->autoProvisionCentralWarehouse();
        }

        $widgetsFile = __DIR__ . '/../../config/widgets.php';
        if (file_exists($widgetsFile)) {
            $widgets = require $widgetsFile;

            foreach ($widgets as $key => $definition) {
                WidgetRegistry::register($key, $definition);
            }
        }

        Livewire::component('market::admin.vendor-form', VendorForm::class);
        Livewire::component('market::vendor.product-form', ProductForm::class);
        Livewire::component('market::vendor.kyc-wizard', KycWizard::class);
        Livewire::component('market::admin.market-settings', MarketSettings::class);
        Livewire::component('market::admin.master-product-form', MasterProductForm::class);
        Livewire::component('market::admin.brand-manager', BrandManager::class);
        Livewire::component('market::admin.category-manager', CategoryManager::class);
        Livewire::component('market::admin.attribute-manager', AttributeManager::class);
        Livewire::component('market::vendor.product-manager', ProductManager::class);
        Livewire::component('market::vendor.vendor-warehouse-manager', VendorWarehouseManager::class);
        Livewire::component('market::admin.vendor-product-review', VendorProductReview::class);
        Livewire::component('market::admin.warehouse-manager', WarehouseManager::class);
        Livewire::component('market::admin.warehouse-stock-controller', WarehouseStockController::class);
        Livewire::component('market::admin.product-variant-selector', ProductVariantSelector::class);
        Livewire::component('market::admin.checkout-form-manager', CheckoutFormManager::class);
        Livewire::component('market::web.cart-manager', CartManager::class);
        Livewire::component('market::web.popup-cart', PopupCart::class);
        Livewire::component('market::web.cart-counter', CartCounter::class);
        Livewire::component('market::web.add-to-cart-button', AddToCartButton::class);
        Livewire::component('market::web.checkout-modal', CheckoutModal::class);
        Livewire::component('market::web.checkout-page', CheckoutPage::class);
        Livewire::component('market::web.location-modal', LocationModal::class);
        Livewire::component('market::user.order-form', OrderForm::class);

        // 💡 ثبت Observer
        Vendor::observe(VendorObserver::class);
    }

    /**
     * 💡 NEW: Automatically create a central warehouse if WMS is active and none exists.
     */
    protected function autoProvisionCentralWarehouse()
    {
        try {
            if (Schema::hasTable('market_settings') && Schema::hasTable('market_warehouses')) {
                if (MarketSetting::getValue('wms.enabled', false)) {
                    Warehouse::firstOrCreate(
                        ['vendor_id' => null],
                        ['name' => 'انبار مرکزی سیستم', 'code' => 'WH-MAIN', 'is_active' => true]
                    );
                }
            }
        } catch (\Throwable $e) {
            // Ignore exceptions during boot phase (e.g. migration not run yet, database config errors, etc.)
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(\Modules\Market\App\Services\Map\MapServiceInterface::class, function ($app) {
            $provider = \Modules\Market\Entities\MarketSetting::getValue('map.provider', 'neshan');
            if ($provider === 'map_ir') {
                return new \Modules\Market\App\Services\Map\MapIrMapService();
            }
            return new \Modules\Market\App\Services\Map\NeshanMapService();
        });
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder','')));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
