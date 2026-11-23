<?php

namespace Modules\Warehouse\App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;
use Modules\Warehouse\Entities\WarehouseMarketLink;
use Modules\Warehouse\Entities\WarehouseSetting;

class WarehouseIntegrationService
{
    /**
     * Check if Market module is installed and active in the system.
     */
    public function isMarketInstalled(): bool
    {
        if (!class_exists(\Modules\Market\Entities\Warehouse::class)) {
            return false;
        }

        try {
            return Module::where('name', 'Market')
                ->where('installed', true)
                ->where('active', true)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if WMS feature is enabled in Market module settings.
     */
    public function isMarketWmsEnabled(): bool
    {
        if (!$this->isMarketInstalled()) {
            return false;
        }

        if (!class_exists(\Modules\Market\Entities\MarketSetting::class)) {
            return false;
        }

        try {
            return (bool) \Modules\Market\Entities\MarketSetting::getValue('wms.enabled', false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if the integration between Warehouse and Market is active.
     */
    public function isIntegrationActive(): bool
    {
        return WarehouseSetting::isMarketIntegrationEnabled() && $this->isMarketWmsEnabled();
    }

    /**
     * Get all Market warehouses with their linked status.
     */
    public function getAvailableMarketWarehouses(): Collection
    {
        if (!$this->isMarketInstalled()) {
            return collect();
        }

        try {
            $linkedMap = WarehouseMarketLink::pluck('warehouse_id', 'market_warehouse_id')->toArray();

            return \Modules\Market\Entities\Warehouse::with('vendor')
                ->get()
                ->map(function ($mw) use ($linkedMap) {
                    $linkedWarehouseId = $linkedMap[$mw->id] ?? null;
                    return [
                        'id' => $mw->id,
                        'name' => $mw->name,
                        'code' => $mw->code,
                        'vendor_name' => $mw->vendor?->store_name ?? 'انبار مرکزی فروشگاه',
                        'is_active' => (bool)$mw->is_active,
                        'is_linked' => $linkedWarehouseId !== null,
                        'linked_warehouse_id' => $linkedWarehouseId,
                    ];
                });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Get Market warehouses linked as sub-warehouses to a specific Warehouse.
     */
    public function getLinkedMarketWarehouses(int $warehouseId, bool $activeOnly = true): Collection
    {
        if (!$this->isMarketInstalled()) {
            return collect();
        }

        try {
            $marketIds = WarehouseMarketLink::where('warehouse_id', $warehouseId)
                ->pluck('market_warehouse_id');

            if ($marketIds->isEmpty()) {
                return collect();
            }

            return \Modules\Market\Entities\Warehouse::with(['vendor', 'stocks'])
                ->whereIn('id', $marketIds)
                ->when($activeOnly, function ($q) {
                    $q->where('is_active', true);
                })
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Sync linked Market warehouses for a given warehouse.
     */
    public function syncLinks(int $warehouseId, array $selectedMarketIds): void
    {
        // Remove existing links for this warehouse
        WarehouseMarketLink::where('warehouse_id', $warehouseId)->delete();

        // Also ensure market_warehouse_id uniqueness
        if (!empty($selectedMarketIds)) {
            WarehouseMarketLink::whereIn('market_warehouse_id', $selectedMarketIds)->delete();

            foreach ($selectedMarketIds as $mId) {
                if ($mId > 0) {
                    WarehouseMarketLink::create([
                        'warehouse_id' => $warehouseId,
                        'market_warehouse_id' => (int) $mId,
                    ]);
                }
            }
        }
    }
}
