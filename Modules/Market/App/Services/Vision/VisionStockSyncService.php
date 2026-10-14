<?php

namespace Modules\Market\App\Services\Vision;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\VendorVisionSetting;
use Modules\Market\Entities\Warehouse;
use Modules\Market\Entities\WarehouseStock;

class VisionStockSyncService
{
    protected VisionSoapClient $client;
    protected VendorVisionSetting $setting;

    public function __construct(VendorVisionSetting $setting)
    {
        $this->setting = $setting;
        $this->client = new VisionSoapClient($setting);
    }

    /**
     * Sync inventory/stock from Vision ERP (Type 20 / Online Stock Data)
     *
     * @return array
     */
    public function syncStock(): array
    {
        try {
            // Type 20 is commonly inventory in Vision PDA
            $response = $this->client->callAction('GetOnLineBaseDataPDA', [
                'type' => 20,
            ]);

            if (!$response['success']) {
                $this->setting->update([
                    'last_sync_at' => now(),
                    'last_sync_status' => 'failed',
                    'last_error_message' => $response['message'],
                ]);

                return [
                    'success' => false,
                    'updated_count' => 0,
                    'message' => $response['message'],
                ];
            }

            $data = $response['data'];
            $items = is_array($data) ? ($data['items'] ?? $data['Data'] ?? $data) : [];

            if (!is_array($items) || empty($items)) {
                $this->setting->update([
                    'last_sync_at' => now(),
                    'last_sync_status' => 'success',
                    'last_error_message' => null,
                ]);

                return [
                    'success' => true,
                    'updated_count' => 0,
                    'message' => 'اطلاعات موجودی کالاها از وب‌سرویس خالی بود.',
                ];
            }

            $updatedCount = 0;
            $vendorId = $this->setting->vendor_id;

            // Target warehouse for this vendor
            $targetWarehouse = null;
            if ($this->setting->default_warehouse_code) {
                $targetWarehouse = Warehouse::where('vendor_id', $vendorId)
                    ->where('code', $this->setting->default_warehouse_code)
                    ->first();
            }

            if (!$targetWarehouse) {
                $targetWarehouse = Warehouse::where('vendor_id', $vendorId)->first();
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $goodCode = (string)($item['GoodCode'] ?? $item['Code'] ?? $item['good_code'] ?? '');
                $qty = (float)($item['Stock'] ?? $item['Qty'] ?? $item['stock'] ?? 0);

                if (empty($goodCode)) {
                    continue;
                }

                // Find MasterProduct by crm_code
                $master = MasterProduct::where('crm_code', $goodCode)->first();
                if (!$master) {
                    continue;
                }

                // Get all variants of this master product
                $variantIds = ProductVariant::where('master_product_id', $master->id)->pluck('id');
                if ($variantIds->isEmpty()) {
                    continue;
                }

                // Update VendorProduct stock directly
                VendorProduct::where('vendor_id', $vendorId)
                    ->whereIn('product_variant_id', $variantIds)
                    ->update(['stock' => max(0, (int)$qty)]);

                // Also update WarehouseStock if warehouse exists
                if ($targetWarehouse) {
                    foreach ($variantIds as $vId) {
                        WarehouseStock::updateOrCreate(
                            [
                                'warehouse_id' => $targetWarehouse->id,
                                'product_variant_id' => $vId,
                            ],
                            [
                                'quantity' => max(0, (int)$qty),
                            ]
                        );
                    }
                }

                $updatedCount++;
            }

            $this->setting->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'success',
                'last_error_message' => null,
            ]);

            return [
                'success' => true,
                'updated_count' => $updatedCount,
                'message' => "موجودی {$updatedCount} کالا با موفقیت از ویژن به‌روزرسانی شد.",
            ];
        } catch (Exception $e) {
            Log::error('Vision Stock Sync Error: ' . $e->getMessage(), ['vendor_id' => $this->setting->vendor_id]);

            $this->setting->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'failed',
                'last_error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'updated_count' => 0,
                'message' => 'خطا در به‌روزرسانی موجودی: ' . $e->getMessage(),
            ];
        }
    }
}
