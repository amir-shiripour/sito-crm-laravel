<?php

namespace Modules\Market\App\Services\Vision;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Clients\Entities\Client;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\VendorVisionSetting;
use Modules\Market\Entities\Warehouse;

class VisionCatalogSyncService
{
    protected VisionSoapClient $client;
    protected VendorVisionSetting $setting;

    public function __construct(VendorVisionSetting $setting)
    {
        $this->setting = $setting;
        $this->client = new VisionSoapClient($setting);
    }

    /**
     * Sync base data types from Vision ERP:
     * Type 0: Units of measurement / پایه
     * Type 1: Products / Goods
     * Type 2: Customers
     * Type 3: Warehouses
     */
    public function syncBaseData(int $type): array
    {
        try {
            $response = $this->client->callAction('GetOnLineBaseDataPDA', [
                'type' => $type,
            ]);

            if (!$response['success']) {
                return [
                    'success' => false,
                    'count' => 0,
                    'message' => $response['message'],
                ];
            }

            $data = $response['data'];
            $items = is_array($data) ? ($data['items'] ?? $data['Data'] ?? $data) : [];

            if (!is_array($items) || empty($items)) {
                return [
                    'success' => true,
                    'count' => 0,
                    'message' => 'هیچ رکوردی برای همگام‌سازی از ویژن یافت نشد.',
                ];
            }

            switch ($type) {
                case 1:
                    return $this->syncProducts($items);
                case 2:
                    return $this->syncCustomers($items);
                case 3:
                    return $this->syncWarehouses($items);
                default:
                    return [
                        'success' => true,
                        'count' => count($items),
                        'message' => 'داده‌ها دریافت شدند اما هندلر اختصاصی برای این نوع تعریف نشده است.',
                    ];
            }
        } catch (Exception $e) {
            Log::error('Vision Catalog Sync Error: ' . $e->getMessage(), ['vendor_id' => $this->setting->vendor_id, 'type' => $type]);
            return [
                'success' => false,
                'count' => 0,
                'message' => 'خطا در همگام‌سازی: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync or match products by CRM Code / Vision Good Code
     */
    protected function syncProducts(array $goods): array
    {
        $matched = 0;
        $updated = 0;

        foreach ($goods as $item) {
            if (!is_array($item)) {
                continue;
            }

            $goodCode = (string)($item['GoodCode'] ?? $item['Code'] ?? $item['good_code'] ?? '');
            $barcode = (string)($item['Barcode'] ?? $item['barcode'] ?? '');
            $title = (string)($item['GoodName'] ?? $item['Name'] ?? $item['title'] ?? '');

            if (empty($goodCode) && empty($barcode)) {
                continue;
            }

            // Find master product by crm_code or barcode (gtin)
            $masterProduct = MasterProduct::where(function ($q) use ($goodCode, $barcode) {
                if ($goodCode) {
                    $q->where('crm_code', $goodCode);
                }
                if ($barcode) {
                    $q->orWhere('gtin', $barcode);
                }
            })->first();

            if ($masterProduct) {
                $matched++;
                if (empty($masterProduct->crm_code) && $goodCode) {
                    $masterProduct->update(['crm_code' => $goodCode]);
                    $updated++;
                }
            }
        }

        return [
            'success' => true,
            'count' => count($goods),
            'matched' => $matched,
            'updated' => $updated,
            'message' => "تعداد {$matched} کالا تطبیق داده شد (بروزرسانی کد: {$updated}).",
        ];
    }

    /**
     * Match Vision customers to CRM clients
     */
    protected function syncCustomers(array $customers): array
    {
        $matched = 0;
        foreach ($customers as $cust) {
            if (!is_array($cust)) {
                continue;
            }

            $custCode = (string)($cust['CustomerCode'] ?? $cust['Code'] ?? '');
            $nationalCode = (string)($cust['NationalCode'] ?? $cust['MelliCode'] ?? '');
            $mobile = (string)($cust['Mobile'] ?? $cust['Phone'] ?? '');

            if (empty($custCode)) {
                continue;
            }

            $client = Client::where(function ($q) use ($nationalCode, $mobile) {
                if (!empty($nationalCode)) {
                    $q->where('national_code', $nationalCode);
                }
                if (!empty($mobile)) {
                    $q->orWhere('phone', $mobile);
                }
            })->first();

            if ($client) {
                if ($client->vision_code !== $custCode) {
                    $client->update(['vision_code' => $custCode]);
                }
                $matched++;
            }
        }

        return [
            'success' => true,
            'count' => count($customers),
            'matched' => $matched,
            'message' => "تعداد {$matched} مشتری با کدهای نرم‌افزار ویژن مطابقت داده شدند.",
        ];
    }

    /**
     * Sync Warehouses from Vision
     */
    protected function syncWarehouses(array $warehouses): array
    {
        $count = 0;
        foreach ($warehouses as $wh) {
            if (!is_array($wh)) {
                continue;
            }

            $whCode = (string)($wh['WarehouseCode'] ?? $wh['Code'] ?? '');
            $whName = (string)($wh['WarehouseName'] ?? $wh['Name'] ?? '');

            if (empty($whCode)) {
                continue;
            }

            Warehouse::firstOrCreate(
                [
                    'vendor_id' => $this->setting->vendor_id,
                    'code' => $whCode,
                ],
                [
                    'name' => $whName ?: 'انبار ویژن ' . $whCode,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        return [
            'success' => true,
            'count' => $count,
            'message' => "تعداد {$count} انبار همگام‌سازی شد.",
        ];
    }
}
