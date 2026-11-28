<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseSetting;
use Modules\Warehouse\App\Services\WarehouseIntegrationService;
use Modules\Warehouse\App\Services\WarehouseValuationService;

class DashboardController extends Controller
{
    protected WarehouseIntegrationService $integrationService;

    public function __construct(WarehouseIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    public function index()
    {
        $inventoryEnabled = WarehouseSetting::isInventoryEnabled();
        $assetsEnabled = WarehouseSetting::isAssetsEnabled();
        $marketIntegrationEnabled = WarehouseSetting::isMarketIntegrationEnabled();
        $isMarketWmsAvailable = $this->integrationService->isMarketWmsEnabled();

        // Active Main Inventory Warehouse and active sub-warehouses
        $mainInventoryWarehouse = null;
        $inventorySubWarehouses = collect();
        $linkedMarketWarehouses = collect();

        if ($inventoryEnabled) {
            $mainInventoryWarehouse = Warehouse::inventory()
                ->main()
                ->active()
                ->with(['creator', 'children' => function ($q) {
                    $q->active()->with('creator')->orderBy('name');
                }])
                ->first();

            if ($mainInventoryWarehouse) {
                $inventorySubWarehouses = $mainInventoryWarehouse->children;

                if ($marketIntegrationEnabled && $isMarketWmsAvailable) {
                    $linkedMarketWarehouses = $this->integrationService->getLinkedMarketWarehouses($mainInventoryWarehouse->id, true);
                }
            }
        }

        // Active Main Asset Warehouse and active sub-warehouses
        $mainAssetWarehouse = null;
        $assetSubWarehouses = collect();

        if ($assetsEnabled) {
            $mainAssetWarehouse = Warehouse::asset()
                ->main()
                ->active()
                ->with(['creator', 'children' => function ($q) {
                    $q->active()->with('creator')->orderBy('name');
                }])
                ->first();

            if ($mainAssetWarehouse) {
                $assetSubWarehouses = $mainAssetWarehouse->children;
            }
        }

        // Active counts only
        $totalInventoryCount = $inventoryEnabled ? Warehouse::inventory()->active()->count() : 0;
        $totalAssetCount = $assetsEnabled ? Warehouse::asset()->active()->count() : 0;
        $totalActiveWarehouses = $totalInventoryCount + $totalAssetCount;
        $linkedMarketCount = $linkedMarketWarehouses->count();

        $valuationService = app(WarehouseValuationService::class);
        $invIds = $inventorySubWarehouses->pluck('id')->when($mainInventoryWarehouse, fn($c) => $c->push($mainInventoryWarehouse->id));
        $stockSummaries = $valuationService->getWarehousesStockSummary($invIds);
        $reorderSummary = $inventoryEnabled
            ? $valuationService->getReorderAlertsReport(['scope' => 'alerts_only'])['summary']
            : null;

        return view('warehouse::dashboard.index', compact(
            'inventoryEnabled',
            'assetsEnabled',
            'marketIntegrationEnabled',
            'isMarketWmsAvailable',
            'mainInventoryWarehouse',
            'inventorySubWarehouses',
            'stockSummaries',
            'reorderSummary',
            'linkedMarketWarehouses',
            'mainAssetWarehouse',
            'assetSubWarehouses',
            'totalInventoryCount',
            'totalAssetCount',
            'totalActiveWarehouses',
            'linkedMarketCount'
        ));
    }
}
