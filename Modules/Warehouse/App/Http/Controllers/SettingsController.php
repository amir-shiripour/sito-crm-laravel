<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseSetting;
use Modules\Warehouse\App\Services\WarehouseIntegrationService;

class SettingsController extends Controller
{
    protected WarehouseIntegrationService $integrationService;

    public function __construct(WarehouseIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Show warehouse settings page.
     */
    public function edit()
    {
        $inventoryEnabled = WarehouseSetting::isInventoryEnabled();
        $assetsEnabled = WarehouseSetting::isAssetsEnabled();
        $marketIntegrationEnabled = WarehouseSetting::isMarketIntegrationEnabled();

        $isMarketInstalled = $this->integrationService->isMarketInstalled();
        $isMarketWmsEnabled = $this->integrationService->isMarketWmsEnabled();

        $availableMarketWarehouses = collect();
        $mainInventoryWarehouse = Warehouse::inventory()->main()->first();

        if ($isMarketInstalled) {
            $availableMarketWarehouses = $this->integrationService->getAvailableMarketWarehouses();
        }

        return view('warehouse::settings.index', compact(
            'inventoryEnabled',
            'assetsEnabled',
            'marketIntegrationEnabled',
            'isMarketInstalled',
            'isMarketWmsEnabled',
            'availableMarketWarehouses',
            'mainInventoryWarehouse'
        ));
    }

    /**
     * Update warehouse settings.
     */
    public function update(Request $request)
    {
        $inventoryEnabled = $request->boolean('inventory_enabled');
        $assetsEnabled = $request->boolean('assets_enabled');
        $marketIntegrationEnabled = $request->boolean('market_integration_enabled');

        // Validation: At least one type must remain enabled
        if (!$inventoryEnabled && !$assetsEnabled) {
            return back()->with('error', 'حداقل یکی از انواع انبار (انبار موجودی یا مدیریت اموال) باید فعال بماند.');
        }

        WarehouseSetting::setValue('inventory_enabled', $inventoryEnabled);
        WarehouseSetting::setValue('assets_enabled', $assetsEnabled);
        WarehouseSetting::setValue('market_integration_enabled', $marketIntegrationEnabled);

        // Handle Market links sync if integration is enabled
        if ($marketIntegrationEnabled && $this->integrationService->isMarketWmsEnabled()) {
            $mainInventory = Warehouse::inventory()->main()->first();
            if ($mainInventory) {
                $selectedMarketIds = $request->input('linked_market_warehouses', []);
                if (!is_array($selectedMarketIds)) {
                    $selectedMarketIds = [];
                }
                $this->integrationService->syncLinks($mainInventory->id, $selectedMarketIds);
            }
        }

        return back()->with('success', 'تنظیمات ماژول انبارداری با موفقیت به‌روزرسانی شد.');
    }
}
