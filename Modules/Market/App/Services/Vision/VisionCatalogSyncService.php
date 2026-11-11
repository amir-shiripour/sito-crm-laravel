<?php

namespace Modules\Market\App\Services\Vision;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Clients\Entities\Client;
use Modules\Market\Entities\Brand;
use Modules\Market\Entities\Category;
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
     * Normalize Arabic/Persian character variations for uniform typography
     */
    protected function normalizePersian(?string $text): string
    {
        if ($text === null) {
            return '';
        }
        $text = str_replace(['ي', 'ك', 'ۀ', 'ة'], ['ی', 'ک', 'ه', 'ه'], $text);
        return trim($text);
    }

    /**
     * Fetch complete Layer 1 metadata from Vision ERP (categories, subcategories up to 4 levels, total counts, price options)
     * without importing any products to the store yet.
     */
    public function fetchDiscoveryMetadata(): array
    {
        try {
            $buyerCode = (string)($this->setting->ws_username ?: '10100177');

            // 1. Fetch Categories / Commodity Groups (Type 1)
            $catRes = $this->client->callAction('GetOnLineBaseDataPDA', [
                'inputStringJson' => json_encode([
                    'type' => 1,
                    'PerPage' => 1000,
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
                    'PerPage' => 1000,
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

            // Index subgroups by ParentId
            $subgroupsByParent = [];
            $allSubgroupsByCode = [];
            foreach ($subgroupsRaw as $sub) {
                $code = (string)($sub['CodeG'] ?? '');
                $parentId = (string)($sub['ParentId'] ?? '');
                $name = $this->normalizePersian($sub['NameG'] ?? '');
                $level = (int)($sub['LevelH'] ?? 1);

                $item = [
                    'CodeG' => $code,
                    'NameG' => $name,
                    'LevelH' => $level,
                    'ParentId' => $parentId,
                ];

                $allSubgroupsByCode[$code] = $item;
                if ($parentId !== '') {
                    $subgroupsByParent[$parentId][] = $item;
                }
            }

            // Recursive function to build nested sub-tree up to 4 levels
            $buildSubTree = function (string $parentId) use (&$buildSubTree, $subgroupsByParent): array {
                $children = $subgroupsByParent[$parentId] ?? [];
                $result = [];
                foreach ($children as $child) {
                    $childCode = (string)$child['CodeG'];
                    $subChildren = $buildSubTree($childCode);
                    $child['children'] = $subChildren;
                    $child['has_children'] = !empty($subChildren);
                    $result[] = $child;
                }
                return $result;
            };

            // Flatten helper to list all descendant subgroups in hierarchical order
            $flattenSubTree = function (array $tree) use (&$flattenSubTree): array {
                $flat = [];
                foreach ($tree as $node) {
                    $children = $node['children'] ?? [];
                    $nodeCopy = $node;
                    unset($nodeCopy['children']);
                    $flat[] = $nodeCopy;
                    if (!empty($children)) {
                        $flat = array_merge($flat, $flattenSubTree($children));
                    }
                }
                return $flat;
            };

            // Build hierarchical groups (Commodity Groups)
            $hierarchicalGroups = [];
            foreach ($groupsRaw as $g) {
                $code = (string)($g['CodeG'] ?? '');
                $name = $this->normalizePersian($g['NameG'] ?? '');

                $subTree = $buildSubTree($code);
                $flatSubs = $flattenSubTree($subTree);

                $hierarchicalGroups[] = [
                    'CodeG' => $code,
                    'NameG' => $name,
                    'subgroups' => $flatSubs, // flattened in depth order for clean UI rendering
                    'subgroups_tree' => $subTree, // nested tree structure
                    'subgroups_count' => count($flatSubs),
                    'all_subgroup_codes' => array_column($flatSubs, 'CodeG'),
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
     * Synchronize Commodity Groups from Vision to Market Brands (/user/market/brands)
     * and Subgroups to Market Categories (/user/market/categories) up to 4 levels of depth,
     * strictly coordinated with Layer 1 Quarantine Whitelist.
     */
    public function syncBrandsAndCategories(): array
    {
        if (!$this->setting->is_active) {
            return [
                'success' => false,
                'message' => 'تنظیمات وب‌سرویس جامع ویژن غیرفعال است. هماهنگ‌سازی تنها در صورت فعال بودن وب‌سرویس انجام می‌شود.',
            ];
        }

        $selectedGroups = (array)($this->setting->selected_groups ?: []);
        $selectedSubgroups = (array)($this->setting->selected_subgroups ?: []);

        if (empty($selectedGroups)) {
            return [
                'success' => false,
                'message' => 'هیچ گروهی در لایه ۱ (هاب قرنطینه) انتخاب نشده است. لطفاً ابتدا در تب تنظیمات ویژن، گروه‌های مجاز را فعال کنید.',
            ];
        }

        try {
            $buyerCode = (string)($this->setting->ws_username ?: '10100177');

            // 1. Fetch Groups (Type 1)
            $catRes = $this->client->callAction('GetOnLineBaseDataPDA', [
                'inputStringJson' => json_encode([
                    'type' => 1,
                    'PerPage' => 1000,
                    'groupe' => -1,
                    'idf' => '',
                    'start' => 1,
                    'buyercode' => $buyerCode,
                ], JSON_UNESCAPED_UNICODE),
            ]);
            $groupsRaw = $catRes['data']['category']['gorohks'] ?? [];

            // 2. Fetch Subgroups (Type 2)
            $subRes = $this->client->callAction('GetOnLineBaseDataPDA', [
                'inputStringJson' => json_encode([
                    'type' => 2,
                    'PerPage' => 1000,
                    'groupe' => -1,
                    'idf' => '',
                    'start' => 1,
                    'buyercode' => $buyerCode,
                ], JSON_UNESCAPED_UNICODE),
            ]);
            $subgroupsRaw = $subRes['data']['subCategory']['subGorohks'] ?? [];

            // Step A: Sync Groups -> Brands (market_brands)
            $syncedBrands = 0;
            $brandsMap = []; // vision_code => Brand

            foreach ($groupsRaw as $g) {
                $gCode = (string)($g['CodeG'] ?? '');
                if (!in_array($gCode, $selectedGroups, true)) {
                    continue; // Skip groups outside Layer 1 whitelist
                }

                $gName = $this->normalizePersian($g['NameG'] ?? '');
                $brand = Brand::firstOrNew(['vision_code' => $gCode]);
                $brand->name = $gName ?: ('برند ' . $gCode);

                if (!$brand->exists) {
                    $slug = Str::slug($brand->name);
                    if (empty($slug) || Brand::where('slug', $slug)->exists()) {
                        $slug = 'b-' . $gCode . '-' . Str::random(4);
                    }
                    $brand->slug = $slug;

                    $lastPrefix = Brand::max('code_prefix') ?? 2999;
                    $brand->code_prefix = $lastPrefix + 1;
                    $brand->is_active = true;
                }
                $brand->save();
                $brandsMap[$gCode] = $brand;
                $syncedBrands++;
            }

            // Step B: Build Parent Map for Subgroups to trace root group and ancestor chains
            $parentMap = []; // code => parentId
            $subByCode = [];
            foreach ($subgroupsRaw as $s) {
                $code = (string)($s['CodeG'] ?? '');
                $parentMap[$code] = (string)($s['ParentId'] ?? '');
                $subByCode[$code] = $s;
            }

            // Function to resolve root group CodeG for any subgroup at any level (1..4)
            $resolveRootGroup = function (string $code) use (&$resolveRootGroup, $parentMap, $selectedGroups): ?string {
                $parentId = $parentMap[$code] ?? null;
                if (!$parentId) {
                    return null;
                }
                if (in_array($parentId, $selectedGroups, true)) {
                    return $parentId;
                }
                if (isset($parentMap[$parentId])) {
                    return $resolveRootGroup($parentId);
                }
                return null;
            };

            // Step C: Filter Subgroups according to Layer 1 Whitelist
            $allowedSubgroups = [];
            $hasSubgroupFilter = !empty($selectedSubgroups);

            foreach ($subgroupsRaw as $s) {
                $sCode = (string)($s['CodeG'] ?? '');
                $rootGroup = $resolveRootGroup($sCode);

                if (!$rootGroup || !in_array($rootGroup, $selectedGroups, true)) {
                    continue; // Not belonging to an active group
                }

                if ($hasSubgroupFilter) {
                    $isExplicitlySelected = in_array($sCode, $selectedSubgroups, true);
                    if (!$isExplicitlySelected) {
                        // Include if it is an ancestor of a selected deeper subgroup
                        $isAncestor = false;
                        foreach ($selectedSubgroups as $selSub) {
                            $curr = $selSub;
                            while (isset($parentMap[$curr]) && $parentMap[$curr] !== '') {
                                if ($parentMap[$curr] === $sCode) {
                                    $isAncestor = true;
                                    break 2;
                                }
                                $curr = $parentMap[$curr];
                            }
                        }
                        if (!$isAncestor) {
                            continue;
                        }
                    }
                }

                $allowedSubgroups[] = $s;
            }

            // Step D: Sort Subgroups strictly by LevelH ascending (Level 1, then Level 2, Level 3, Level 4)
            usort($allowedSubgroups, function ($a, $b) {
                return ((int)($a['LevelH'] ?? 1)) <=> ((int)($b['LevelH'] ?? 1));
            });

            // Step E: Sync Subgroups -> Categories (market_categories)
            $syncedCategories = 0;
            $categoryMap = []; // vision_code => Category

            // Pre-load existing categories by vision_code
            foreach (Category::whereNotNull('vision_code')->get() as $existingCat) {
                $categoryMap[$existingCat->vision_code] = $existingCat;
            }

            foreach ($allowedSubgroups as $sub) {
                $sCode = (string)($sub['CodeG'] ?? '');
                $sParentId = (string)($sub['ParentId'] ?? '');
                $sName = $this->normalizePersian($sub['NameG'] ?? '');
                $level = (int)($sub['LevelH'] ?? 1);

                $cat = $categoryMap[$sCode] ?? Category::firstOrNew(['vision_code' => $sCode]);
                $cat->name = $sName ?: ('دسته ' . $sCode);

                if ($level === 1) {
                    // Level 1: Parent is the Brand (Group)
                    $brand = $brandsMap[$sParentId] ?? Brand::where('vision_code', $sParentId)->first();
                    $cat->brand_id = $brand?->id;
                    $cat->parent_id = null; // Main top category under this brand
                } else {
                    // Level 2, 3, 4: Parent is another Category
                    $parentCat = $categoryMap[$sParentId] ?? Category::where('vision_code', $sParentId)->first();
                    $cat->parent_id = $parentCat?->id;
                    $cat->brand_id = $parentCat?->brand_id ?? ($brandsMap[$resolveRootGroup($sCode)]?->id ?? null);
                }

                if (!$cat->exists) {
                    $slug = Str::slug($cat->name);
                    if (empty($slug) || Category::where('slug', $slug)->exists()) {
                        $slug = 'c-' . str_replace([',', ' '], '-', $sCode) . '-' . Str::random(4);
                    }
                    $cat->slug = $slug;

                    $lastOffset = Category::max('code_offset') ?? 0;
                    $cat->code_offset = $lastOffset + 10000;
                    $cat->is_active = true;
                }

                $cat->save();
                $categoryMap[$sCode] = $cat;
                $syncedCategories++;
            }

            // Step F: Ensure every active Brand has at least a root Category (especially groups with no subgroups)
            foreach ($brandsMap as $gCode => $brand) {
                if (!isset($categoryMap[$gCode])) {
                    $cat = Category::firstOrNew(['vision_code' => $gCode]);
                    $cat->name = $brand->name;
                    $cat->brand_id = $brand->id;
                    $cat->parent_id = null;
                    if (!$cat->exists) {
                        $slug = Str::slug($cat->name);
                        if (empty($slug) || Category::where('slug', $slug)->exists()) {
                            $slug = 'c-' . $gCode . '-' . Str::random(4);
                        }
                        $cat->slug = $slug;
                        $lastOffset = Category::max('code_offset') ?? 0;
                        $cat->code_offset = $lastOffset + 10000;
                        $cat->is_active = true;
                        $syncedCategories++;
                    }
                    $cat->save();
                    $categoryMap[$gCode] = $cat;
                }
            }

            return [
                'success' => true,
                'brands_count' => $syncedBrands,
                'categories_count' => $syncedCategories,
                'message' => "هماهنگ‌سازی ساختار با موفقیت انجام شد: {$syncedBrands} برند در «مدیریت برندها» و {$syncedCategories} دسته‌بندی در ساختار درختی «دسته‌بندی‌ها» ایجاد/بروزرسانی شد.",
            ];

        } catch (Exception $e) {
            Log::error('Vision Sync Brands & Categories Error: ' . $e->getMessage(), ['vendor_id' => $this->setting->vendor_id]);
            return [
                'success' => false,
                'brands_count' => 0,
                'categories_count' => 0,
                'message' => 'خطا در هماهنگ‌سازی برندها و دسته‌ها: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync base data types from Vision ERP v3.6.1:
     * Type 0: Customer info (buyer)
     * Type 1: Categories (category.gorohks) -> Syncs Brands
     * Type 2: Subcategories (subCategory.subGorohks) -> Syncs Categories
     * Type 3: Products / Kala (kala.kalas)
     * Type 100: Total products count
     */
    public function syncBaseData(int $type, int $perPage = 100, int $start = 1): array
    {
        if (!$this->setting->is_active) {
            return [
                'success' => false,
                'count' => 0,
                'message' => 'تنظیمات وب‌سرویس جامع ویژن غیرفعال است.',
            ];
        }

        try {
            // For type 1 (Brands) or type 2 (Categories), invoke unified hierarchy sync
            if ($type === 1 || $type === 2) {
                $res = $this->syncBrandsAndCategories();
                return [
                    'success' => $res['success'],
                    'count' => ($res['brands_count'] ?? 0) + ($res['categories_count'] ?? 0),
                    'message' => $res['message'],
                ];
            }

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
     * Sync or match products by CRM Code / Vision Good Code, respecting Layer 1 Whitelist & Price Mapping,
     * connecting to the corresponding Brand (/user/market/brands) and Category (/user/market/categories).
     */
    protected function syncProducts(array $goods): array
    {
        if (!$this->setting->is_active) {
            return [
                'success' => false,
                'count' => 0,
                'message' => 'تنظیمات وب‌سرویس جامع ویژن غیرفعال است.',
            ];
        }

        $matched = 0;
        $updated = 0;
        $created = 0;
        $skipped = 0;

        $selectedGroups = (array)($this->setting->selected_groups ?: []);
        $selectedSubgroups = (array)($this->setting->selected_subgroups ?: []);
        $hasGroupFilter = !empty($selectedGroups);
        $hasSubgroupFilter = !empty($selectedSubgroups);

        $priceField = $this->setting->price_field ?: 'Pricef1';
        $comparePriceField = $this->setting->compare_price_field;
        $initialStatus = $this->setting->initial_product_status ?: 'draft';
        $vendorId = $this->setting->vendor_id;

        // Preload brands and categories with vision_code
        $brandsByVision = Brand::whereNotNull('vision_code')->get()->keyBy('vision_code');
        $categoriesByVision = Category::whereNotNull('vision_code')->get()->keyBy('vision_code');

        foreach ($goods as $item) {
            if (!is_array($item)) {
                continue;
            }

            $goodCode = (string)($item['GoodCode'] ?? $item['Code'] ?? $item['good_code'] ?? '');
            $barcode = (string)($item['Barcode'] ?? $item['barcode'] ?? '');
            $title = $this->normalizePersian($item['GoodName'] ?? $item['Name'] ?? $item['title'] ?? '');
            $groupCode = (string)($item['Gorohk'] ?? '');

            if (empty($goodCode) && empty($barcode)) {
                continue;
            }

            // Determine Brand & Category from groupCode
            $brandId = null;
            $categoryId = null;
            $rootGroupCode = null;

            if (str_contains($groupCode, ',')) {
                // Subgroup (e.g. 106,1 or 106,1,2)
                $cat = $categoriesByVision->get($groupCode);
                if ($cat) {
                    $categoryId = $cat->id;
                    $brandId = $cat->brand_id;
                    $rootGroupCode = $cat->brand?->vision_code;
                } else {
                    // Fallback: extract root group prefix
                    $rootGroupCode = explode(',', $groupCode)[0];
                    $brand = $brandsByVision->get($rootGroupCode);
                    $brandId = $brand?->id;
                }

                // Subgroup filter check
                if ($hasSubgroupFilter && !in_array($groupCode, $selectedSubgroups, true)) {
                    $skipped++;
                    continue;
                }
            } else {
                // Main Group (e.g. 28)
                $rootGroupCode = $groupCode;
                $brand = $brandsByVision->get($groupCode);
                $brandId = $brand?->id;
                $cat = $categoriesByVision->get($groupCode);
                $categoryId = $cat?->id;
            }

            // Layer 1 Quarantine Whitelist Check
            if ($hasGroupFilter && (!in_array($rootGroupCode, $selectedGroups, true))) {
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

            // Find or initialize MasterProduct:
            // Match primarily on barcode ("شناسه محصول (کد داخلی / بارکد)"), or crm_code, or gtin
            $masterProduct = MasterProduct::where(function ($q) use ($goodCode, $barcode) {
                if ($goodCode) {
                    $q->where('barcode', $goodCode);
                    $q->orWhere('crm_code', $goodCode);
                }
                if ($barcode) {
                    $q->orWhere('gtin', $barcode);
                }
            })->first();

            if ($masterProduct) {
                $matched++;
                $dirty = false;
                if ($goodCode && $masterProduct->barcode !== $goodCode) {
                    $masterProduct->barcode = $goodCode;
                    $dirty = true;
                }
                if ($barcode && empty($masterProduct->gtin)) {
                    $masterProduct->gtin = $barcode;
                    $dirty = true;
                }
                if ($brandId && $masterProduct->brand_id !== $brandId) {
                    $masterProduct->brand_id = $brandId;
                    $dirty = true;
                }
                if ($categoryId && $masterProduct->category_id !== $categoryId) {
                    $masterProduct->category_id = $categoryId;
                    $dirty = true;
                }
                if ($dirty) {
                    $masterProduct->save();
                    $updated++;
                }

                // Sync Variant & VendorProduct
                $variant = ProductVariant::firstOrCreate(
                    ['master_product_id' => $masterProduct->id],
                    [
                        'variant_code' => $goodCode ?: ($barcode ?: 'VIS-' . $masterProduct->id),
                        'variant_attributes' => ['title' => 'اصلی'],
                        'price' => $price,
                        'stock' => max(0, (int)$stock),
                        'is_active' => true,
                    ]
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
                        $vendorProduct->status = ($initialStatus === 'published') ? 'published' : 'draft';
                    }
                    $vendorProduct->save();
                }

            } else {
                // Generate Smart SKU (crm_code)
                $smartSku = null;
                if ($brandId && $categoryId) {
                    try {
                        $smartSku = (new \Modules\Market\App\Services\ProductService())->generateCrmCode($brandId, $categoryId);
                    } catch (\Throwable $e) {
                        $smartSku = null;
                    }
                }
                if (empty($smartSku) || MasterProduct::where('crm_code', $smartSku)->exists()) {
                    $smartSku = 'SIT-' . ($goodCode ?: time()) . '-' . Str::random(3);
                }

                // Create new MasterProduct in Layer 1 (as draft or published per setting)
                $slug = Str::slug($title);
                if (empty($slug) || MasterProduct::where('slug', $slug)->exists()) {
                    $slug = 'p-' . ($goodCode ?: time()) . '-' . Str::random(4);
                }

                $newMaster = MasterProduct::create([
                    'title' => $title ?: ('کالای ویژن ' . $goodCode),
                    'slug' => $slug,
                    'crm_code' => $smartSku,
                    'barcode' => $goodCode, // Directly maps Vision product code to "شناسه محصول (کد داخلی / بارکد)"!
                    'gtin' => $barcode ?: null, // Maps global barcode to "شناسه جهانی کالا (GTIN/UPC/EAN)"!
                    'brand_id' => $brandId,
                    'category_id' => $categoryId,
                    'status' => $initialStatus,
                    'weight' => (int)($item['Vazn'] ?? 0),
                ]);

                $variant = ProductVariant::create([
                    'master_product_id' => $newMaster->id,
                    'variant_code' => $goodCode ?: 'VIS-' . $newMaster->id,
                    'variant_attributes' => ['title' => 'اصلی'],
                    'price' => $price,
                    'stock' => max(0, (int)$stock),
                    'is_active' => true,
                ]);

                if ($vendorId) {
                    VendorProduct::create([
                        'vendor_id' => $vendorId,
                        'product_variant_id' => $variant->id,
                        'price' => $price,
                        'discount_price' => $comparePrice ? $price : null,
                        'stock' => max(0, (int)$stock),
                        'status' => ($initialStatus === 'published') ? 'published' : 'draft',
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
            'message' => "همگام‌سازی کالاها انجام شد: {$matched} تطبیق‌یافته، {$created} جدید، {$updated} کد/برند اصلاح‌شده (تعداد {$skipped} قلم خارج از محدوده قرنطینه لایه ۱ نادیده گرفته شد).",
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
            $whName = $this->normalizePersian($wh['WarehouseName'] ?? $wh['Name'] ?? '');

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
