<?php

namespace Modules\Warehouse\App\Services;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseCategory;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseItemBatch;
use Modules\Warehouse\Entities\WarehouseItemStock;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use SimpleXMLElement;

class WarehouseExcelImportService
{
    protected WarehouseCodeService $codeService;
    protected WarehouseValuationService $valuationService;

    public function __construct(
        WarehouseCodeService $codeService,
        WarehouseValuationService $valuationService
    ) {
        $this->codeService = $codeService;
        $this->valuationService = $valuationService;
    }

    /**
     * Import items from an Excel (.xlsx) file.
     */
    public function import(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("فایل اکسل در مسیر مورد نظر یافت نشد.");
        }

        $sheetsData = $this->parseXlsxFile($filePath);
        $result = [
            'total_rows' => 0,
            'imported_items' => 0,
            'updated_items' => 0,
            'skipped_items' => 0,
            'categories_created' => 0,
            'opening_batches_created' => 0,
            'errors' => [],
        ];

        // 1. Process "تنظیمات" (Settings) sheet if present to populate categories/subcategories
        $settingsRows = $sheetsData['تنظیمات'] ?? [];
        if (!empty($settingsRows)) {
            $result['categories_created'] += $this->importCategoriesFromSettings($settingsRows);
        }

        // 2. Identify the items sheet (prefer "تعریف کالا", otherwise first sheet with data)
        $itemsRows = $sheetsData['تعریف کالا'] ?? [];
        if (empty($itemsRows)) {
            foreach ($sheetsData as $sName => $sRows) {
                if ($sName !== 'تنظیمات' && count($sRows) > 1) {
                    $itemsRows = $sRows;
                    break;
                }
            }
        }

        if (empty($itemsRows)) {
            $result['errors'][] = 'شیت تعریف کالا در فایل یافت نشد.';
            return $result;
        }

        // Find header row (usually row 2 in Dr. Zabihi's file, or row 1)
        $headerIndex = 0;
        foreach ($itemsRows as $idx => $row) {
            $joined = implode(' ', array_map('strval', $row));
            if (mb_strpos($joined, 'دسته') !== false || mb_strpos($joined, 'کد کالا') !== false || mb_strpos($joined, 'نام کالا') !== false) {
                $headerIndex = $idx;
                break;
            }
        }

        $headerRow = $itemsRows[$headerIndex] ?? [];
        $headerMap = $this->mapHeaders($headerRow);

        $mainWarehouse = Warehouse::inventory()->main()->first();
        if (!$mainWarehouse) {
            $mainWarehouse = Warehouse::create([
                'name' => 'انبار مرکزی موجودی',
                'type' => 'inventory',
                'is_main' => true,
                'is_active' => true,
                'color' => '#4f46e5',
            ]);
        }

        $categoryCache = [];

        // Loop over data rows
        $dataRows = array_slice($itemsRows, $headerIndex + 1);
        $result['total_rows'] = count($dataRows);

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowIdx => $row) {
                // Skip completely empty rows
                if (!array_filter($row, fn($c) => $c !== '' && $c !== null)) {
                    continue;
                }

                $getVal = function ($key) use ($row, $headerMap) {
                    $col = $headerMap[$key] ?? null;
                    return ($col !== null && isset($row[$col])) ? trim((string)$row[$col]) : null;
                };

                $catName = $getVal('category');
                $subcatName = $getVal('subcategory');
                $brand = $getVal('brand');
                $type = $getVal('type');
                $spec = $getVal('spec');
                $unit = $getVal('unit') ?: 'عدد';
                $code = $getVal('code');
                $name = $getVal('name');
                $reorderPoint = (float)($getVal('reorder_point') ?: 0);
                $minStock = (float)($getVal('min_stock') ?: 0);
                $openingStock = (float)($getVal('opening_stock') ?: 0);
                $price = (float)($getVal('price') ?: 0);
                $location = $getVal('location');
                $description = $getVal('description');

                // Extract brand code and type code
                $brandCode = null;
                if (!empty($brand) && preg_match('/\((.*?)\)/', $brand, $mb)) {
                    $brandCode = trim($mb[1]);
                }
                $typeCode = !empty($type) ? trim(preg_replace('/[^a-zA-Z0-9]/', '', $type)) : null;

                $catCode = null;
                if (!empty($catName) && preg_match('/\((.*?)\)/', $catName, $mc)) {
                    $catCode = trim($mc[1]);
                }
                $subcatCode = null;
                if (!empty($subcatName) && preg_match('/\((.*?)\)/', $subcatName, $ms)) {
                    $subcatCode = trim($ms[1]);
                }

                // If code or name is missing, generate them automatically!
                if (empty($code)) {
                    $code = $this->codeService->generateSuggestedSku($catCode, $subcatCode, $typeCode, $brandCode, $spec);
                }
                if (empty($name)) {
                    $name = $this->codeService->generateSuggestedName($subcatName ?: $catName, $type, $brand, $spec);
                }

                if (empty($name) && empty($code)) {
                    $result['skipped_items']++;
                    continue;
                }

                // Resolve Category and Subcategory
                $categoryId = null;
                $subcategoryId = null;

                if (!empty($catName)) {
                    if (!isset($categoryCache['cat:' . $catName])) {
                        $cat = WarehouseCategory::firstOrCreate(
                            ['name' => $catName, 'parent_id' => null],
                            ['is_active' => true]
                        );
                        $categoryCache['cat:' . $catName] = $cat->id;
                    }
                    $categoryId = $categoryCache['cat:' . $catName];

                    if (!empty($subcatName)) {
                        $subKey = 'sub:' . $categoryId . ':' . $subcatName;
                        if (!isset($categoryCache[$subKey])) {
                            $subcat = WarehouseCategory::firstOrCreate(
                                ['name' => $subcatName, 'parent_id' => $categoryId],
                                ['is_active' => true]
                            );
                            $categoryCache[$subKey] = $subcat->id;
                        }
                        $subcategoryId = $categoryCache[$subKey];
                    }
                }

                // Check if item already exists by code
                $item = null;
                if (!empty($code)) {
                    $item = WarehouseItem::where('code', $code)->first();
                }

                if (!$item) {
                    $systemCode = $this->codeService->generateSystemCode();
                    if (empty($code)) {
                        $code = $systemCode;
                    }

                    $item = WarehouseItem::create([
                        'system_code' => $systemCode,
                        'code' => $code,
                        'name' => $name ?: $code,
                        'category_id' => $categoryId,
                        'subcategory_id' => $subcategoryId,
                        'brand' => $brand,
                        'brand_code' => $brandCode,
                        'item_type' => $type,
                        'item_type_code' => $typeCode,
                        'model_spec' => $spec,
                        'unit' => $unit,
                        'min_stock' => $minStock,
                        'reorder_point' => $reorderPoint,
                        'current_price' => $price > 0 ? $price : null,
                        'last_purchase_price' => $price > 0 ? $price : null,
                        'default_location' => $location,
                        'description' => $description,
                        'is_active' => true,
                    ]);

                    $result['imported_items']++;
                } else {
                    $item->update([
                        'name' => $name ?: $item->name,
                        'category_id' => $categoryId ?: $item->category_id,
                        'subcategory_id' => $subcategoryId ?: $item->subcategory_id,
                        'brand' => $brand ?: $item->brand,
                        'brand_code' => $brandCode ?: $item->brand_code,
                        'item_type' => $type ?: $item->item_type,
                        'item_type_code' => $typeCode ?: $item->item_type_code,
                        'model_spec' => $spec ?: $item->model_spec,
                        'unit' => $unit ?: $item->unit,
                        'min_stock' => $minStock > 0 ? $minStock : $item->min_stock,
                        'reorder_point' => $reorderPoint > 0 ? $reorderPoint : $item->reorder_point,
                        'current_price' => $price > 0 ? $price : $item->current_price,
                        'default_location' => $location ?: $item->default_location,
                        'description' => $description ?: $item->description,
                    ]);

                    $result['updated_items']++;
                }

                // If opening stock > 0, register opening batch if no active batches exist yet
                if ($openingStock > 0 && $item->batches()->count() === 0) {
                    $this->valuationService->addBatch(
                        $item,
                        $mainWarehouse->id,
                        $openingStock,
                        $price,
                        'OPENING-' . $item->system_code,
                        now()->toDateString(),
                        null,
                        'موجودی اول دوره',
                        'ثبت خودکار از فایل اکسل'
                    );
                    $result['opening_batches_created']++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $result['errors'][] = 'خطا در حین ورود اطلاعات: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Map header strings to standard field keys
     */
    protected function mapHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $colIdx => $text) {
            $t = trim(mb_strtolower((string)$text));

            if (mb_strpos($t, 'کد کالا') !== false || $t === 'sku' || $t === 'code') {
                $map['code'] = $colIdx;
            } elseif (mb_strpos($t, 'نام کالا') !== false || $t === 'title' || $t === 'name') {
                $map['name'] = $colIdx;
            } elseif (mb_strpos($t, 'زیردسته') !== false || mb_strpos($t, 'زیر دسته') !== false) {
                $map['subcategory'] = $colIdx;
            } elseif (mb_strpos($t, 'دسته') !== false) {
                $map['category'] = $colIdx;
            } elseif (mb_strpos($t, 'جنس') !== false || mb_strpos($t, 'نوع') !== false || mb_strpos($t, 'تیپ') !== false || mb_strpos($t, 'تایپ') !== false) {
                $map['type'] = $colIdx;
            } elseif (mb_strpos($t, 'برند') !== false || mb_strpos($t, 'سازنده') !== false) {
                $map['brand'] = $colIdx;
            } elseif (mb_strpos($t, 'سایز') !== false || mb_strpos($t, 'رنگ') !== false || mb_strpos($t, 'مدل') !== false) {
                $map['spec'] = $colIdx;
            } elseif (mb_strpos($t, 'واحد') !== false) {
                $map['unit'] = $colIdx;
            } elseif (mb_strpos($t, 'حداقل موجودی') !== false || mb_strpos($t, 'موجودی ایمنی') !== false) {
                $map['min_stock'] = $colIdx;
            } elseif (mb_strpos($t, 'نقطه سفارش') !== false) {
                $map['reorder_point'] = $colIdx;
            } elseif (mb_strpos($t, 'موجودی ابتدای دوره') !== false || mb_strpos($t, 'موجودی اول دوره') !== false) {
                $map['opening_stock'] = $colIdx;
            } elseif (mb_strpos($t, 'قیمت روز') !== false || mb_strpos($t, 'قیمت') !== false || mb_strpos($t, 'فی') !== false) {
                $map['price'] = $colIdx;
            } elseif (mb_strpos($t, 'موقعیت') !== false) {
                $map['location'] = $colIdx;
            } elseif (mb_strpos($t, 'مشخصات') !== false || mb_strpos($t, 'مختصات') !== false || mb_strpos($t, 'توضیحات') !== false) {
                $map['description'] = $colIdx;
            }
        }
        return $map;
    }

    /**
     * Import Categories & Subcategories from "تنظیمات" sheet
     */
    protected function importCategoriesFromSettings(array $settingsRows): int
    {
        $created = 0;
        if (count($settingsRows) < 2) return 0;

        $headers = $settingsRows[1] ?? [];
        $catCol = array_search('دسته', $headers);
        $subcatCol = array_search('زیردسته', $headers);

        $catList = [];
        $subcatList = [];

        foreach (array_slice($settingsRows, 2) as $row) {
            if ($catCol !== false && !empty($row[$catCol])) {
                $catList[] = trim($row[$catCol]);
            }
            if ($subcatCol !== false && !empty($row[$subcatCol])) {
                $subcatList[] = trim($row[$subcatCol]);
            }
        }

        foreach (array_unique($catList) as $catName) {
            $cat = WarehouseCategory::firstOrCreate(
                ['name' => $catName, 'parent_id' => null],
                ['is_active' => true]
            );
            if ($cat->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Parse .xlsx file into sheet arrays using native PHP ZipArchive & SimpleXML
     */
    public function parseXlsxFile(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception("امکان باز کردن فایل فشرده اکسل وجود ندارد.");
        }

        // Shared strings
        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $sstXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            foreach ($sstXml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } else {
                    $parts = [];
                    foreach ($si->r as $r) {
                        $parts[] = (string)$r->t;
                    }
                    $sharedStrings[] = implode('', $parts);
                }
            }
        }

        // Sheets metadata
        $wbXml = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $sheets = [];
        foreach ($wbXml->sheets->sheet as $s) {
            $sheets[] = (string)$s['name'];
        }

        $result = [];
        foreach ($sheets as $idx => $sheetName) {
            $sheetFile = 'xl/worksheets/sheet' . ($idx + 1) . '.xml';
            if ($zip->locateName($sheetFile) === false) {
                continue;
            }

            $sheetXml = simplexml_load_string($zip->getFromName($sheetFile));
            $sheetRows = [];

            if (isset($sheetXml->sheetData->row)) {
                foreach ($sheetXml->sheetData->row as $row) {
                    $rowVals = [];
                    foreach ($row->c as $c) {
                        $val = isset($c->v) ? (string)$c->v : '';
                        if (isset($c['t']) && (string)$c['t'] === 's' && $val !== '') {
                            $sIdx = (int)$val;
                            $val = $sharedStrings[$sIdx] ?? $val;
                        }
                        $rowVals[] = $val;
                    }
                    $sheetRows[] = $rowVals;
                }
            }

            $result[$sheetName] = $sheetRows;
        }

        $zip->close();
        return $result;
    }
}
