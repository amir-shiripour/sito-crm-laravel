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
     * Fetch complete Layer 1 metadata from Vision ERP (categories, subcategories, total counts, price options)
     * without importing any products to the store yet.
     */
    public function fetchDiscoveryMetadata(): array
    {
        try {
            $buyerCode = (string)($this->setting->ws_username ?: '10100177');

            // 1. Fetch Categories (Type 1)
            $catRes = $this->client->callAction('GetOnLineBaseDataPDA', [
                'inputStringJson' => json_encode([
                    'type' => 1,
                    'PerPage' => 500,
                    'groupe' => -1,
                    'idf' => '',
                    'start' => 1,
                    'buyercode' => $buyerCode,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            $groupsRaw = $catRes['data']['category']['gorohks'] ?? [];

            // 2. Fetch SubCategories (Type 2)
            $subRes = $this->client->callAction('GetOnLineBaseDataPDA', [
                'inputStringJson' => json_encode([
                    'type' => 2,
                    'PerPage' => 500,
                    'groupe' => -1,
                    'idf' => '',
                    'start' => 1,
                    'buyercode' => $buyerCode,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            $subgroupsRaw = $subRes['data']['subCategory']['subGorohks'] ?? [];

            // 3. Fetch Total Kala Count (Type 100)
            $countRes = $this->client->callAction('GetOnLineBaseDataPDA', [
                'inputStringJson' => json_encode([
                    'type' => 100,
                    'PerPage' => 10,
                    'groupe' => -1,
                    'idf' => '',
                    'start' => 1,
                    'buyercode' => $buyerCode,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            $totalCounts = (int)($countRes['data']['kalaCounts'] ?? 0);

            // Group subgroups by ParentId
            $subgroupsByParent = [];
            foreach ($subgroupsRaw as $sub) {
                $parentId = (string)($sub['ParentId'] ?? '');
                if ($parentId !== '') {
                    $subgroupsByParent[$parentId][] = [
                        'CodeG' => (string)($sub['CodeG'] ?? ''),
                        'NameG' => (string)($sub['NameG'] ?? ''),
                        'LevelH' => (int)($sub['LevelH'] ?? 1),
                        'ParentId' => $parentId,
                    ];
                }
            }

            // Build hierarchical groups
            $hierarchicalGroups = [];
            foreach ($groupsRaw as $g) {
                $code = (string)($g['CodeG'] ?? '');
                $name = (string)($g['NameG'] ?? '');
                $subs = $subgroupsByParent[$code] ?? [];

                $hierarchicalGroups[] = [
                    'CodeG' => $code,
                    'NameG' => $name,
                    'subgroups' => $subs,
                    'subgroups_count' => count($subs),
                ];
            }

            $priceFields = [
                'Pricef1' => 'کلاس قیمت ۱ (عمده / نمایندگی)',
                'Pricef2' => 'کلاس قیمت ۲ (همکار / فروشگاه)',
                'Pricef3' => 'کلاس قیمت ۳',
                'Pricef4' => 'کلاس قیمت ۴',
                'Pricef5' => 'کلاس قیمت ۵',
                'Pricef6' => 'کلاس قیمت ۶',
                'Pricef7' => 'کلاس قیمت ۷',
                'Pricef8' => 'کلاس قیمت ۸',
                'Pricef9' => 'کلاس قیمت ۹',
                'Pricef10' => 'کلاس قیمت ۱۰',
                'Pricef11' => 'کلاس قیمت ۱۱',
                'Pricef12' => 'کلاس قیمت ۱۲',
                'PriceF' => 'قیمت مصرف‌کننده (PriceF)',
            ];

            $metadata = [
                'groups' => $hierarchicalGroups,
                'groups_count' => count($hierarchicalGroups),
                'subgroups_count' => count($subgroupsRaw),
                'total_goods_count' => $totalCounts,
                'price_fields' => $priceFields,
                'fetched_at' => now()->toDateTimeString(),
            ];

            $this->setting->update([
                'cached_metadata' => $metadata,
                'last_sync_at' => now(),
                'last_sync_status' => 'success',
                'last_error_message' => null,
            ]);

            return [
                'success' => true,
                'data' => $metadata,
                'message' => "ساختار اطلاعات پایه ویژن با موفقیت واکشی شد (تعداد " . count($hierarchicalGroups) . " گروه، " . count($subgroupsRaw) . " زیرگروه و {$totalCounts} قلم کالا).",
            ];

        } catch (Exception $e) {
            Log::error('Vision Discovery Metadata Error: ' . $e->getMessage(), ['vendor_id' => $this->setting->vendor_id]);
            return [
                'success' => false,
                'data' => null,
                'message' => 'خطا در واکشی ساختار اطلاعات ویژن: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync base data types from Vision ERP v3.6.1:
     * Type 0: Customer info (buyer)
     * Type 1: Categories (category.gorohks)
     * Type 2: Subcategories (subCategory.subGorohks)
     * Type 3: Products / Kala (kala.kalas)
     * Type 100: Total products count
     */
    public function syncBaseData(int $type, int $perPage = 100, int $start = 1): array
    {
        try {
            $buyerCode = (string)($this->setting->ws_username ?: '10100177');

            $payload = [
                'type' => $type,
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
                return [
                    'success' => false,
                    'count' => 0,
                    'message' => $response['message'],
                ];
            }

            $data = $response['data'];
            if (is_string($data)) {
                $data = json_decode($data, true) ?: [];
            }

            // Unpack items based on Vision ERP 3.6.1 response schema
            $items = [];
            if ($type === 3) {
                $items = $data['kala']['kalas'] ?? [];
            } elseif ($type === 1) {
                $items = $data['category']['gorohks'] ?? [];
            } elseif ($type === 2) {
                $items = $data['subCategory']['subGorohks'] ?? [];
            } elseif ($type === 0) {
                $items = $data['buyer']['buyers'] ?? [];
            } elseif ($type === 100) {
                $count = (int)($data['kalaCounts'] ?? 0);
                return [
                    'success' => true,
                    'count' => $count,
                    'message' => "تعداد کل اقلام کالا در ویژن: {$count}",
                ];
            } else {
                $items = $data['items'] ?? $data['Data'] ?? $data;
            }

            if (!is_array($items) || empty($items)) {
                return [
                    'success' => true,
                    'count' => 0,
                    'message' => 'هیچ رکوردی برای همگام‌سازی از ویژن یافت نشد.',
                ];
            }

            switch ($type) {
                case 3:
                    return $this->syncProducts($items);
                case 0:
                    return $this->syncCustomers($items);
                case 1:
                case 2:
                    return [
                        'success' => true,
                        'count' => count($items),
                        'message' => "تعداد " . count($items) . " دسته‌بندی دریافت شد.",
                    ];
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
     * Sync or match products by CRM Code / Vision Good Code, respecting Layer 1 Whitelist & Price Mapping
     */
    protected function syncProducts(array $goods): array
    {
        $matched = 0;
        $updated = 0;
        $created = 0;
        $skipped = 0;

        $selectedGroups = (array)($this->setting->selected_groups ?: []);
        $selectedSubgroups = (array)($this->setting->selected_subgroups ?: []);
        $hasGroupFilter = !empty($selectedGroups);

        $priceField = $this->setting->price_field ?: 'Pricef1';
        $comparePriceField = $this->setting->compare_price_field;
        $initialStatus = $this->setting->initial_product_status ?: 'draft';
        $vendorId = $this->setting->vendor_id;

        foreach ($goods as $item) {
            if (!is_array($item)) {
                continue;
            }

            $goodCode = (string)($item['GoodCode'] ?? $item['Code'] ?? $item['good_code'] ?? '');
            $barcode = (string)($item['Barcode'] ?? $item['barcode'] ?? '');
            $title = (string)($item['GoodName'] ?? $item['Name'] ?? $item['title'] ?? '');
            $groupCode = (string)($item['Gorohk'] ?? '');

            if (empty($goodCode) && empty($barcode)) {
                continue;
            }

            // Layer 1 Filter: If user selected specific groups, skip goods outside active groups
            if ($hasGroupFilter && !in_array($groupCode, $selectedGroups, true)) {
                $skipped++;
                continue;
            }

            // Calculate price based on selected price field
            $price = (float)($item[$priceField] ?? 0);
            if ($price <= 0) {
                // Fallback to consumer price PriceF or Expr1 if Pricefx was 0
                $price = (float)($item['PriceF'] ?? $item['Expr1'] ?? 0);
            }

            $comparePrice = null;
            if ($comparePriceField && isset($item[$comparePriceField])) {
                $cp = (float)$item[$comparePriceField];
                if ($cp > $price) {
                    $comparePrice = $cp;
                }
            }

            $stock = (float)($item['AllT'] ?? $item['MasterT'] ?? $item['SlaveT'] ?? 0);

            // Find or initialize MasterProduct
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
                $dirty = false;
                if (empty($masterProduct->crm_code) && $goodCode) {
                    $masterProduct->crm_code = $goodCode;
                    $dirty = true;
                }
                if (empty($masterProduct->gtin) && $barcode) {
                    $masterProduct->gtin = $barcode;
                    $dirty = true;
                }
                if ($dirty) {
                    $masterProduct->save();
                    $updated++;
                }

                // Sync Variant & VendorProduct
                $variant = ProductVariant::firstOrCreate(
                    ['master_product_id' => $masterProduct->id],
                    ['sku' => $goodCode ?: ($barcode ?: 'VIS-' . $masterProduct->id)]
                );

                if ($vendorId) {
                    $vendorProduct = VendorProduct::firstOrNew([
                        'vendor_id' => $vendorId,
                        'product_variant_id' => $variant->id,
                    ]);

                    if ($price > 0) {
                        $vendorProduct->price = $price;
                    }
                    if ($comparePrice) {
                        $vendorProduct->discount_price = $price;
                    }
                    $vendorProduct->stock = max(0, (int)$stock);
                    if (!$vendorProduct->exists) {
                        $vendorProduct->status = ($initialStatus === 'published') ? 'approved' : 'pending';
                    }
                    $vendorProduct->save();
                }

            } else {
                // Create new MasterProduct in Layer 1 (as draft or published per setting)
                $slug = \Illuminate\Support\Str::slug($title);
                if (empty($slug) || MasterProduct::where('slug', $slug)->exists()) {
                    $slug = 'p-' . ($goodCode ?: time()) . '-' . \Illuminate\Support\Str::random(4);
                }

                $newMaster = MasterProduct::create([
                    'title' => $title ?: ('کالای ویژن ' . $goodCode),
                    'slug' => $slug,
                    'crm_code' => $goodCode,
                    'gtin' => $barcode ?: null,
                    'status' => $initialStatus,
                    'weight' => (int)($item['Vazn'] ?? 0),
                ]);

                $variant = ProductVariant::create([
                    'master_product_id' => $newMaster->id,
                    'sku' => $goodCode ?: 'VIS-' . $newMaster->id,
                ]);

                if ($vendorId) {
                    VendorProduct::create([
                        'vendor_id' => $vendorId,
                        'product_variant_id' => $variant->id,
                        'price' => $price,
                        'discount_price' => $comparePrice ? $price : null,
                        'stock' => max(0, (int)$stock),
                        'status' => ($initialStatus === 'published') ? 'approved' : 'pending',
                    ]);
                }

                $created++;
            }
        }

        return [
            'success' => true,
            'count' => count($goods),
            'matched' => $matched,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'message' => "همگام‌سازی کالاها انجام شد: {$matched} تطبیق‌یافته، {$created} جدید، {$updated} کد اصلاح‌شده (تعداد {$skipped} قلم خارج از دسته‌های فعال نادیده گرفته شد).",
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
