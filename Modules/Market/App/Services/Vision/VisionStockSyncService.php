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
     * Sync inventory/stock from Vision ERP (Type 3 / Online Goods with AllT Stock)
     *
     * @return array
     */
    public function syncStock(): array
    {
        if (!$this->setting->is_active) {
            return [
                'success' => false,
                'updated_count' => 0,
                'message' => 'تنظیمات وب‌سرویس جامع ویژن غیرفعال است.',
            ];
        }

        @set_time_limit(600);
        @ignore_user_abort(true);

        try {
            $buyerCode = (string)($this->setting->ws_username ?: '10100177');

            // Paginated fetch of all items across catalog
            $items = [];
            $perPage = 500;
            $page = 1;
            $seenCodes = [];
            $maxPages = 30;

            while ($page <= $maxPages) {
                $start = 1 + (($page - 1) * $perPage);
                $payload = [
                    'type' => 3,
                    'PerPage' => $perPage,
                    'groupe' => -1,
                    'idf' => '',
                    'start' => $start,
                    'buyercode' => $buyerCode,
                ];

                $response = $this->client->callAction('GetOnLineBaseDataPDA', [
                    'inputStringJson' => json_encode($payload, JSON_UNESCAPED_UNICODE),
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
                if (is_string($data)) {
                    $data = json_decode($data, true) ?: [];
                }

                $batch = $data['kala']['kalas'] ?? $data['items'] ?? [];
                if (!is_array($batch) || empty($batch)) {
                    break;
                }

                $newInBatch = 0;
                foreach ($batch as $item) {
                    $c = (string)($item['GoodCode'] ?? $item['Code'] ?? $item['good_code'] ?? '');
                    if ($c === '' || !isset($seenCodes[$c])) {
                        if ($c !== '') {
                            $seenCodes[$c] = true;
                        }
                        $items[] = $item;
                        $newInBatch++;
                    }
                }

                if (count($batch) < $perPage || $newInBatch === 0) {
                    break;
                }

                $page++;
            }

            if (empty($items)) {
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

            $isWmsActive = (bool) \Modules\Market\Entities\MarketSetting::getValue('wms.enabled', false);

            // Target warehouse for this vendor when WMS is active
            $targetWarehouse = null;
            if ($isWmsActive) {
                if ($this->setting->crm_warehouse_id) {
                    $targetWarehouse = Warehouse::find($this->setting->crm_warehouse_id);
                }
                if (!$targetWarehouse && $this->setting->default_warehouse_code) {
                    $targetWarehouse = Warehouse::where('vendor_id', $vendorId)
                        ->where('code', $this->setting->default_warehouse_code)
                        ->first();
                }
                if (!$targetWarehouse) {
                    $targetWarehouse = Warehouse::where('vendor_id', $vendorId)->where('is_active', true)->first();
                }
                if (!$targetWarehouse) {
                    $targetWarehouse = Warehouse::whereNull('vendor_id')->where('is_active', true)->first();
                }
                if (!$targetWarehouse && $vendorId) {
                    $vendor = $this->setting->vendor;
                    $storeName = $vendor ? ($vendor->store_name ?: ($vendor->user->name ?? 'فروشنده ' . $vendorId)) : 'فروشنده ' . $vendorId;
                    $slugPrefix = ($vendor && $vendor->slug) ? strtoupper(substr($vendor->slug, 0, 10)) : 'VND' . $vendorId;
                    $targetWarehouse = Warehouse::firstOrCreate(
                        ['vendor_id' => $vendorId],
                        [
                            'name' => 'انبار اصلی ' . $storeName,
                            'code' => 'WH-' . $slugPrefix,
                            'is_active' => true,
                        ]
                    );
                }
            }

            $selectedGroups = (array)($this->setting->selected_groups ?: []);
            $selectedSubgroups = (array)($this->setting->selected_subgroups ?: []);
            $hasGroupFilter = !empty($selectedGroups);
            $hasSubgroupFilter = !empty($selectedSubgroups);

            $priceField = $this->setting->price_field ?: 'Pricef1';
            $comparePriceField = $this->setting->compare_price_field;
            $stockField = $this->setting->stock_field ?: 'AllT';

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $goodCode = (string)($item['GoodCode'] ?? $item['Code'] ?? $item['good_code'] ?? '');
                $groupCode = (string)($item['Gorohk'] ?? '');

                if (empty($goodCode)) {
                    continue;
                }

                // Layer 1 Quarantine Whitelist Check
                if (str_contains($groupCode, ',')) {
                    $rootGroup = explode(',', $groupCode)[0];
                    if ($hasGroupFilter && !in_array($rootGroup, $selectedGroups, true)) {
                        continue;
                    }
                    if ($hasSubgroupFilter && !in_array($groupCode, $selectedSubgroups, true)) {
                        continue;
                    }
                } else {
                    $rootGroup = $groupCode;
                    if ($hasGroupFilter && !in_array($rootGroup, $selectedGroups, true)) {
                        continue;
                    }
                }

                // Quantity from selected Vision stock model
                $qty = (float)($item[$stockField] ?? $item['AllT'] ?? $item['Stock'] ?? $item['Qty'] ?? 0);
                $stockQty = max(0, (int)$qty);

                // Price calculation based on Layer 1 mapping
                $basePrice = (float)($item[$priceField] ?? 0);
                if ($basePrice <= 0) {
                    $basePrice = (float)($item['PriceF'] ?? $item['Expr1'] ?? 0);
                }

                $comparePrice = null;
                if ($comparePriceField && isset($item[$comparePriceField])) {
                    $cp = (float)$item[$comparePriceField];
                    if ($cp > $basePrice) {
                        $comparePrice = $cp;
                    }
                }

                $regularPrice = $comparePrice ?: $basePrice;
                $discountPrice = $comparePrice ? $basePrice : null;

                // Find MasterProduct by barcode (Vision GoodCode / کد داخلی) or crm_code
                $master = MasterProduct::where('barcode', $goodCode)
                    ->orWhere('crm_code', $goodCode)
                    ->first();
                if (!$master) {
                    continue;
                }

                // Get all variants of this master product
                $variants = ProductVariant::where('master_product_id', $master->id)->get();
                if ($variants->isEmpty()) {
                    continue;
                }

                foreach ($variants as $variant) {
                    // Update variant price & stock
                    $vUpdates = ['stock' => $stockQty];
                    if ($regularPrice > 0) {
                        $vUpdates['price'] = $regularPrice;
                    }
                    $vUpdates['discount_price'] = $discountPrice;
                    $variant->update($vUpdates);

                    // Update VendorProduct price & stock
                    if ($vendorId) {
                        $vendorProduct = VendorProduct::firstOrNew([
                            'vendor_id' => $vendorId,
                            'product_variant_id' => $variant->id,
                        ]);

                        if ($regularPrice > 0) {
                            $vendorProduct->price = $regularPrice;
                        }
                        $vendorProduct->discount_price = $discountPrice;
                        $vendorProduct->stock = $stockQty;
                        if (!$vendorProduct->exists) {
                            $vendorProduct->status = 'published';
                        }
                        $vendorProduct->save();

                        // WMS inventory update & transaction
                        if ($isWmsActive && $targetWarehouse) {
                            $warehouseStock = WarehouseStock::firstOrNew([
                                'warehouse_id' => $targetWarehouse->id,
                                'product_variant_id' => $variant->id,
                                'vendor_product_id' => $vendorProduct->id,
                            ]);

                            $oldPhysical = (int)($warehouseStock->physical_stock ?? 0);
                            $diff = $stockQty - $oldPhysical;

                            $warehouseStock->physical_stock = $stockQty;
                            $warehouseStock->online_stock = $stockQty;
                            $warehouseStock->save();

                            if ($diff !== 0) {
                                \Modules\Market\Entities\WarehouseTransaction::create([
                                    'warehouse_id' => $targetWarehouse->id,
                                    'product_variant_id' => $variant->id,
                                    'vendor_product_id' => $vendorProduct->id,
                                    'type' => 'adjustment',
                                    'quantity' => $diff,
                                    'reference_type' => VendorVisionSetting::class,
                                    'reference_id' => $this->setting->id,
                                    'description' => "به‌روزرسانی خودکار موجودی از وب‌سرویس جامع ویژن (موجودی قبلی: {$oldPhysical}، موجودی جدید: {$stockQty})",
                                    'user_id' => auth()->id() ?? $this->setting->vendor?->user_id,
                                ]);
                            }
                        }
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
