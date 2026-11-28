<?php

namespace Modules\Warehouse\App\Services;

use Modules\Clients\Entities\Client;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseCategory;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseItemBatch;
use Modules\Warehouse\Entities\WarehouseItemStock;
use Modules\Warehouse\Entities\WarehousePurchaseReceipt;
use Modules\Warehouse\Entities\WarehouseTransferRequest;
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

        // 3. Also process "ورودی خرید" sheet if it has populated rows
        $purchaseRows = $sheetsData['ورودی خرید'] ?? [];
        if (!empty($purchaseRows)) {
            $purchaseRes = $this->importPurchasesFromRows($purchaseRows);
            $result['purchase_receipts_created'] = $purchaseRes['receipts_created'];
            $result['purchase_lines_imported'] = $purchaseRes['lines_imported'];
            if (!empty($purchaseRes['errors'])) {
                $result['errors'] = array_merge($result['errors'], $purchaseRes['errors']);
            }
        }

        // 4. Also process "درخواست کالا" sheet if it has populated rows
        $transferRows = $sheetsData['درخواست کالا'] ?? [];
        if (!empty($transferRows)) {
            $transferRes = $this->importTransfersFromRows($transferRows);
            $result['transfers_created'] = $transferRes['transfers_created'];
            $result['transfer_lines_imported'] = $transferRes['lines_imported'];
            if (!empty($transferRes['errors'])) {
                $result['errors'] = array_merge($result['errors'], $transferRes['errors']);
            }
        }

        return $result;
    }

    /**
     * Import transfer requests from a standalone Excel file (or workbook containing "درخواست کالا").
     */
    public function importTransfers(string $filePath, bool $deliverImmediately = true): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception('فایل اکسل در مسیر مورد نظر یافت نشد.');
        }

        $sheetsData = $this->parseXlsxFile($filePath);
        $transferRows = $sheetsData['درخواست کالا'] ?? [];

        if (empty($transferRows)) {
            foreach ($sheetsData as $sRows) {
                if (count($sRows) > 1) {
                    $transferRows = $sRows;
                    break;
                }
            }
        }

        if (empty($transferRows)) {
            return [
                'transfers_created' => 0,
                'lines_imported' => 0,
                'skipped_rows' => 0,
                'errors' => ['هیچ داده‌ای در فایل یا شیت «درخواست کالا» یافت نشد.'],
            ];
        }

        return $this->importTransfersFromRows($transferRows, $deliverImmediately);
    }

    /**
     * Parse and execute transfer requests matching Excel columns:
     * تاریخ | شماره درخواست | انبارک درخواست‌کننده | کد کالا | نام کالا | تعداد | درخواست‌کننده | توضیحات
     */
    public function importTransfersFromRows(array $rows, bool $deliverImmediately = true): array
    {
        $result = [
            'transfers_created' => 0,
            'lines_imported' => 0,
            'skipped_rows' => 0,
            'errors' => [],
        ];

        $headerIndex = null;
        foreach ($rows as $idx => $row) {
            $joined = implode(' ', array_map('strval', $row));
            if (
                (mb_strpos($joined, 'کد کالا') !== false || mb_strpos($joined, 'نام کالا') !== false)
                && (mb_strpos($joined, 'تعداد') !== false)
                && (mb_strpos($joined, 'درخواست') !== false || mb_strpos($joined, 'انبارک') !== false || mb_strpos($joined, 'مقصد') !== false)
            ) {
                $headerIndex = $idx;
                break;
            }
        }

        if ($headerIndex === null) {
            return $result;
        }

        $headerMap = $this->mapTransferHeaders($rows[$headerIndex]);
        $dataRows = array_slice($rows, $headerIndex + 1);

        /** @var WarehousePurchaseService $purchaseService */
        $purchaseService = app(WarehousePurchaseService::class);
        /** @var WarehouseTransferService $transferService */
        $transferService = app(WarehouseTransferService::class);

        $warehouses = Warehouse::inventory()->active()->get();
        $mainWarehouse = Warehouse::inventory()->main()->active()->first() ?? $warehouses->first();

        $groupedTransfers = [];

        foreach ($dataRows as $offset => $row) {
            if (!array_filter($row, fn($c) => trim((string)$c) !== '')) {
                continue;
            }

            $excelRowNumber = $headerIndex + 2 + $offset;
            $getVal = function ($key) use ($row, $headerMap) {
                $col = $headerMap[$key] ?? null;
                return ($col !== null && isset($row[$col])) ? trim((string)$row[$col]) : '';
            };

            $rawDate = $getVal('date');
            $rawNeededDate = $getVal('needed_date');
            $requestNumber = $getVal('request_number');
            $destWhName = $getVal('destination_warehouse');
            $srcWhName = $getVal('source_warehouse');
            $itemCode = $getVal('code');
            $itemName = $getVal('name');
            $rawQty = $purchaseService->normalizeDigits(str_replace([',', '،', ' '], '', $getVal('quantity')));
            $requesterName = $getVal('requester');
            $notes = $getVal('notes');

            $qty = (float)$rawQty;
            if ($qty <= 0) {
                $result['skipped_rows']++;
                continue;
            }

            $item = null;
            if ($itemCode !== '') {
                $item = WarehouseItem::where('code', $itemCode)
                    ->orWhere('system_code', $itemCode)
                    ->first();
            }
            if (!$item && $itemName !== '') {
                $item = WarehouseItem::where('name', $itemName)->first();
            }
            if (!$item) {
                $result['errors'][] = "ردیف {$excelRowNumber}: کالایی با کد «{$itemCode}» یا نام «{$itemName}» یافت نشد.";
                continue;
            }

            $destWarehouse = $warehouses->first(function ($w) use ($destWhName) {
                return $destWhName !== '' && (trim($w->name) === $destWhName || strcasecmp(trim((string)$w->code), $destWhName) === 0);
            }) ?: ($destWhName !== '' ? $warehouses->first(fn($w) => mb_strpos($w->name, $destWhName) !== false) : null);

            if (!$destWarehouse) {
                $result['errors'][] = "ردیف {$excelRowNumber}: انبارک درخواست‌کننده «{$destWhName}» در سیستم یافت نشد.";
                continue;
            }

            $srcWarehouse = $mainWarehouse;
            if ($srcWhName !== '') {
                $foundSrc = $warehouses->first(function ($w) use ($srcWhName) {
                    return trim($w->name) === $srcWhName || strcasecmp(trim((string)$w->code), $srcWhName) === 0;
                }) ?: $warehouses->first(fn($w) => mb_strpos($w->name, $srcWhName) !== false);
                if ($foundSrc) {
                    $srcWarehouse = $foundSrc;
                }
            }

            if (!$srcWarehouse || $srcWarehouse->id === $destWarehouse->id) {
                $result['errors'][] = "ردیف {$excelRowNumber}: انبار مبدأ و انبارک مقصد یکسان یا نامعتبر است.";
                continue;
            }

            try {
                $gregorianDate = $purchaseService->parseDate($rawDate) ?? now()->toDateString();
                $gregorianNeededDate = $rawNeededDate !== '' ? $purchaseService->parseDate($rawNeededDate) : null;
            } catch (\Throwable $e) {
                $result['errors'][] = "ردیف {$excelRowNumber}: " . $e->getMessage();
                continue;
            }

            if ($requestNumber !== '') {
                $duplicate = WarehouseTransferRequest::where('request_number', $requestNumber)
                    ->whereDate('request_date', $gregorianDate)
                    ->whereIn('status', ['pending', 'delivered'])
                    ->exists();
                if ($duplicate) {
                    $result['skipped_rows']++;
                    continue;
                }
            }

            $groupKey = $gregorianDate . '|' . ($requestNumber !== '' ? $requestNumber : 'ROW_' . $excelRowNumber) . '|' . $destWarehouse->id;

            if (!isset($groupedTransfers[$groupKey])) {
                $groupedTransfers[$groupKey] = [
                    'header' => [
                        'request_date' => $gregorianDate,
                        'needed_date' => $gregorianNeededDate,
                        'request_number' => $requestNumber ?: null,
                        'source_warehouse_id' => $srcWarehouse->id,
                        'destination_warehouse_id' => $destWarehouse->id,
                        'requester_name' => $requesterName ?: null,
                        'notes' => 'ثبت‌شده از درون‌ریزی اکسل',
                    ],
                    'lines' => [],
                ];
            }

            $groupedTransfers[$groupKey]['lines'][] = [
                'item_id' => $item->id,
                'quantity' => $qty,
                'notes' => $notes ?: null,
            ];
        }

        if (empty($groupedTransfers)) {
            return $result;
        }

        DB::beginTransaction();
        try {
            foreach ($groupedTransfers as $group) {
                $transfer = $transferService->createTransfer($group['header'], $group['lines'], $deliverImmediately);
                $result['transfers_created']++;
                $result['lines_imported'] += $transfer->total_items_count;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $result['errors'][] = 'خطا در اجرای انتقال از اکسل: ' . $e->getMessage();
        }

        return $result;
    }

    protected function mapTransferHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $colIdx => $text) {
            $t = trim(mb_strtolower((string)$text));
            if ($t === '') {
                continue;
            }

            if (mb_strpos($t, 'مورد نیاز') !== false || mb_strpos($t, 'تاریخ نیاز') !== false || mb_strpos($t, 'زمان نیاز') !== false || $t === 'needed_date') {
                $map['needed_date'] = $colIdx;
            } elseif (mb_strpos($t, 'تاریخ') !== false || $t === 'date') {
                $map['date'] = $colIdx;
            } elseif (mb_strpos($t, 'شماره درخواست') !== false || mb_strpos($t, 'شماره حواله') !== false || $t === 'request_number') {
                $map['request_number'] = $colIdx;
            } elseif (mb_strpos($t, 'انبارک') !== false || mb_strpos($t, 'مقصد') !== false || $t === 'destination') {
                $map['destination_warehouse'] = $colIdx;
            } elseif (mb_strpos($t, 'مبدا') !== false || mb_strpos($t, 'مبدأ') !== false || $t === 'source') {
                $map['source_warehouse'] = $colIdx;
            } elseif (mb_strpos($t, 'کد کالا') !== false || $t === 'sku' || $t === 'code') {
                $map['code'] = $colIdx;
            } elseif (mb_strpos($t, 'نام کالا') !== false || $t === 'name') {
                $map['name'] = $colIdx;
            } elseif (mb_strpos($t, 'تعداد') !== false || $t === 'qty' || $t === 'quantity') {
                $map['quantity'] = $colIdx;
            } elseif (mb_strpos($t, 'درخواست‌کننده') !== false || mb_strpos($t, 'درخواست کننده') !== false || $t === 'requester') {
                $map['requester'] = $colIdx;
            } elseif (mb_strpos($t, 'توضیح') !== false || $t === 'notes') {
                $map['notes'] = $colIdx;
            }
        }
        return $map;
    }

    /**
     * Import goods issue / patient consumption rows from Excel (matching Central OR Sub-warehouses).
     */
    public function importConsumptions(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception('فایل اکسل در مسیر مورد نظر یافت نشد.');
        }

        $sheetsData = $this->parseXlsxFile($filePath);
        $rows = $sheetsData['مصرف کالا']
            ?? $sheetsData['خروج کالا']
            ?? $sheetsData['خروجی کالا']
            ?? $sheetsData['ثبت مصرف']
            ?? [];

        if (empty($rows)) {
            foreach ($sheetsData as $sRows) {
                if (count($sRows) > 1) {
                    $rows = $sRows;
                    break;
                }
            }
        }

        if (empty($rows)) {
            return [
                'documents_created' => 0,
                'lines_imported' => 0,
                'skipped_rows' => 0,
                'errors' => ['هیچ شیت معتبری برای ثبت خروج و مصرف در فایل اکسل یافت نشد.'],
            ];
        }

        $consumptionService = app(WarehouseConsumptionService::class);
        $warehouses = Warehouse::inventory()->active()->get();
        $mainWarehouse = $warehouses->firstWhere('is_main', true);

        $result = [
            'documents_created' => 0,
            'lines_imported' => 0,
            'skipped_rows' => 0,
            'errors' => [],
        ];

        $headerIdx = 0;
        $colMap = [];
        foreach ($rows as $idx => $row) {
            $rowStr = implode(' ', array_map('strval', $row));
            if (mb_strpos($rowStr, 'کد کالا') !== false || mb_strpos($rowStr, 'نام کالا') !== false) {
                $headerIdx = $idx;
                $colMap = $this->mapConsumptionHeaders($row);
                break;
            }
        }

        if (empty($colMap)) {
            $result['errors'][] = 'ستون‌های الزامی (کد کالا / نام کالا / تعداد کالا) در ردیف سرستون یافت نشد.';
            return $result;
        }

        $groupedIssues = [];
        foreach (array_slice($rows, $headerIdx + 1, null, true) as $rowIdx => $row) {
            $excelRowNumber = $rowIdx + 1;
            $getVal = function ($key) use ($row, $colMap) {
                if (!isset($colMap[$key])) {
                    return '';
                }
                return trim((string)($row[$colMap[$key]] ?? ''));
            };

            $itemCode = $getVal('code');
            $itemName = $getVal('name');
            $rawQty = str_replace([',', '،', ' '], '', $getVal('quantity'));
            $rawDate = $getVal('date');
            $patientName = $getVal('patient_name');
            $caseNumber = $getVal('case_number');
            $warehouseName = $getVal('warehouse');
            $notes = $getVal('notes');

            if ($itemCode === '' && $itemName === '' && $rawQty === '') {
                continue;
            }

            $qty = (float)$rawQty;
            if ($qty <= 0) {
                $result['skipped_rows']++;
                continue;
            }

            $item = null;
            if ($itemCode !== '') {
                $item = WarehouseItem::where('code', $itemCode)
                    ->orWhere('system_code', $itemCode)
                    ->first();
            }
            if (!$item && $itemName !== '') {
                $item = WarehouseItem::where('name', $itemName)->first();
            }

            if (!$item) {
                $result['errors'][] = "ردیف {$excelRowNumber}: کالایی با کد «{$itemCode}» یا نام «{$itemName}» یافت نشد.";
                continue;
            }

            // Match BOTH main central warehouse and sub-warehouses
            $sourceWarehouse = null;
            if ($warehouseName !== '') {
                $sourceWarehouse = $warehouses->first(function ($w) use ($warehouseName) {
                    return trim($w->name) === $warehouseName || strcasecmp(trim((string)$w->code), $warehouseName) === 0;
                }) ?: $warehouses->first(fn($w) => mb_strpos($w->name, $warehouseName) !== false);

                if (!$sourceWarehouse) {
                    $result['errors'][] = "ردیف {$excelRowNumber}: انبار یا انبارک «{$warehouseName}» یافت نشد.";
                    continue;
                }
            } else {
                $sourceWarehouse = $mainWarehouse ?: $warehouses->first();
            }

            if (!$sourceWarehouse) {
                $result['errors'][] = "ردیف {$excelRowNumber}: هیچ انبار موجودی فعالی در سیستم تعریف نشده است.";
                continue;
            }

            try {
                $gregorianDate = $consumptionService->parseDate($rawDate) ?? now()->toDateString();
            } catch (\Throwable $e) {
                $result['errors'][] = "ردیف {$excelRowNumber}: " . $e->getMessage();
                continue;
            }

            $clientId = null;
            if ($caseNumber !== '') {
                $client = Client::where('case_number', $caseNumber)->first();
                if ($client) {
                    $clientId = $client->id;
                    $patientName = $patientName ?: $client->full_name;
                }
            }
            if (!$clientId && $patientName !== '') {
                $client = Client::where('full_name', $patientName)->first();
                if ($client) {
                    $clientId = $client->id;
                    $caseNumber = $caseNumber ?: $client->case_number;
                }
            }

            $groupKey = $gregorianDate
                . '|' . ($caseNumber !== '' ? $caseNumber : ($patientName !== '' ? $patientName : 'ROW_' . $excelRowNumber))
                . '|' . $sourceWarehouse->id;

            if (!isset($groupedIssues[$groupKey])) {
                $groupedIssues[$groupKey] = [
                    'header' => [
                        'issue_date' => $gregorianDate,
                        'issue_type' => ($patientName !== '' || $caseNumber !== '') ? 'patient_consumption' : 'department_consumption',
                        'source_warehouse_id' => $sourceWarehouse->id,
                        'client_id' => $clientId,
                        'patient_name' => $patientName ?: null,
                        'patient_case_number' => $caseNumber ?: null,
                        'notes' => 'ثبت‌شده از درون‌ریزی اکسل خروج و مصرف کالا',
                    ],
                    'lines' => [],
                ];
            }

            $groupedIssues[$groupKey]['lines'][] = [
                'item_id' => $item->id,
                'warehouse_id' => $sourceWarehouse->id,
                'quantity' => $qty,
                'notes' => $notes ?: null,
            ];
        }

        if (empty($groupedIssues)) {
            return $result;
        }

        DB::beginTransaction();
        try {
            foreach ($groupedIssues as $group) {
                $doc = $consumptionService->createIssue($group['header'], $group['lines']);
                $result['documents_created']++;
                $result['lines_imported'] += $doc->items_count;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $result['errors'][] = 'خطا در ثبت اسناد خروج و مصرف از اکسل: ' . $e->getMessage();
        }

        return $result;
    }

    protected function mapConsumptionHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $colIdx => $text) {
            $t = trim(mb_strtolower((string)$text));
            if ($t === '') {
                continue;
            }

            if (mb_strpos($t, 'تاریخ') !== false || $t === 'date') {
                $map['date'] = $colIdx;
            } elseif (mb_strpos($t, 'کد کالا') !== false || $t === 'sku' || $t === 'code') {
                $map['code'] = $colIdx;
            } elseif (mb_strpos($t, 'نام کالا') !== false || $t === 'name') {
                $map['name'] = $colIdx;
            } elseif (mb_strpos($t, 'تعداد') !== false || $t === 'qty' || $t === 'quantity') {
                $map['quantity'] = $colIdx;
            } elseif (mb_strpos($t, 'بیمار') !== false || $t === 'patient') {
                $map['patient_name'] = $colIdx;
            } elseif (mb_strpos($t, 'پرونده') !== false || $t === 'case_number') {
                $map['case_number'] = $colIdx;
            } elseif (mb_strpos($t, 'انبار') !== false || $t === 'warehouse') {
                $map['warehouse'] = $colIdx;
            } elseif (mb_strpos($t, 'توضیح') !== false || $t === 'notes') {
                $map['notes'] = $colIdx;
            }
        }
        return $map;
    }

    /**
     * Import purchase receipts from a standalone Excel file (or workbook containing "ورودی خرید").
     */
    public function importPurchases(string $filePath, bool $updateCurrentPrices = false): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception('فایل اکسل در مسیر مورد نظر یافت نشد.');
        }

        $sheetsData = $this->parseXlsxFile($filePath);
        $purchaseRows = $sheetsData['ورودی خرید'] ?? [];

        if (empty($purchaseRows)) {
            foreach ($sheetsData as $sName => $sRows) {
                if (count($sRows) > 1) {
                    $purchaseRows = $sRows;
                    break;
                }
            }
        }

        if (empty($purchaseRows)) {
            return [
                'receipts_created' => 0,
                'lines_imported' => 0,
                'skipped_rows' => 0,
                'errors' => ['هیچ داده‌ای در فایل یا شیت «ورودی خرید» یافت نشد.'],
            ];
        }

        return $this->importPurchasesFromRows($purchaseRows, $updateCurrentPrices);
    }

    /**
     * Parse and create purchase receipts from sheet rows matching Excel columns:
     * تاریخ | شماره فاکتور | کد کالا | نام کالا | تعداد | فی | انبار مقصد | توضیحات
     */
    public function importPurchasesFromRows(array $rows, bool $updateCurrentPrices = false): array
    {
        $result = [
            'receipts_created' => 0,
            'lines_imported' => 0,
            'skipped_rows' => 0,
            'errors' => [],
        ];

        $headerIndex = null;
        foreach ($rows as $idx => $row) {
            $joined = implode(' ', array_map('strval', $row));
            if (
                (mb_strpos($joined, 'کد کالا') !== false || mb_strpos($joined, 'نام کالا') !== false)
                && (mb_strpos($joined, 'تعداد') !== false || mb_strpos($joined, 'فاکتور') !== false || mb_strpos($joined, 'فی') !== false)
            ) {
                $headerIndex = $idx;
                break;
            }
        }

        if ($headerIndex === null) {
            return $result;
        }

        $headerMap = $this->mapPurchaseHeaders($rows[$headerIndex]);
        $dataRows = array_slice($rows, $headerIndex + 1);

        /** @var WarehousePurchaseService $purchaseService */
        $purchaseService = app(WarehousePurchaseService::class);
        $warehouses = Warehouse::inventory()->active()->get();
        $mainWarehouseId = Warehouse::inventory()->main()->active()->value('id')
            ?: Warehouse::inventory()->active()->value('id');

        $groupedReceipts = [];

        foreach ($dataRows as $offset => $row) {
            if (!array_filter($row, fn($c) => trim((string)$c) !== '')) {
                continue;
            }

            $excelRowNumber = $headerIndex + 2 + $offset;
            $getVal = function ($key) use ($row, $headerMap) {
                $col = $headerMap[$key] ?? null;
                return ($col !== null && isset($row[$col])) ? trim((string)$row[$col]) : '';
            };

            $rawDate = $getVal('date');
            $invoiceNumber = $getVal('invoice_number');
            $itemCode = $getVal('code');
            $itemName = $getVal('name');
            $rawQty = $purchaseService->normalizeDigits(str_replace([',', '،', ' '], '', $getVal('quantity')));
            $rawPrice = $purchaseService->normalizeDigits(str_replace([',', '،', ' '], '', $getVal('unit_price')));
            $warehouseName = $getVal('warehouse');
            $supplierName = $getVal('supplier');
            $rawExpiry = $getVal('expiry_date');
            $notes = $getVal('notes');

            $qty = (float)$rawQty;
            $unitPrice = (float)($rawPrice !== '' ? $rawPrice : 0);

            if ($qty <= 0) {
                $result['skipped_rows']++;
                continue;
            }

            $item = null;
            if ($itemCode !== '') {
                $item = WarehouseItem::where('code', $itemCode)
                    ->orWhere('system_code', $itemCode)
                    ->first();
            }
            if (!$item && $itemName !== '') {
                $item = WarehouseItem::where('name', $itemName)->first();
            }

            if (!$item) {
                $result['errors'][] = "ردیف {$excelRowNumber}: کالایی با کد «{$itemCode}» یا نام «{$itemName}» در شناسنامه کالاها یافت نشد.";
                continue;
            }

            try {
                $gregorianDate = $purchaseService->parseDate($rawDate) ?? now()->toDateString();
            } catch (\Throwable $e) {
                $result['errors'][] = "ردیف {$excelRowNumber}: " . $e->getMessage();
                continue;
            }

            $expiryDate = null;
            if ($rawExpiry !== '') {
                try {
                    $expiryDate = $purchaseService->parseDate($rawExpiry);
                } catch (\Throwable $e) {
                    $result['errors'][] = "ردیف {$excelRowNumber} (تاریخ انقضا): " . $e->getMessage();
                    continue;
                }
            }

            // Resolve destination warehouse
            $targetWarehouseId = null;
            if ($warehouseName !== '') {
                $matchedWh = $warehouses->first(function ($w) use ($warehouseName) {
                    return trim($w->name) === $warehouseName || strcasecmp(trim((string)$w->code), $warehouseName) === 0;
                });
                if (!$matchedWh) {
                    $matchedWh = $warehouses->first(fn($w) => mb_strpos($w->name, $warehouseName) !== false);
                }
                $targetWarehouseId = $matchedWh?->id;
            }
            $targetWarehouseId = $targetWarehouseId ?: ($item->default_warehouse_id ?: $mainWarehouseId);

            // Prevent duplicate import when invoice_number + purchase_date already exist
            if ($invoiceNumber !== '') {
                $duplicateExists = WarehousePurchaseReceipt::where('invoice_number', $invoiceNumber)
                    ->whereDate('purchase_date', $gregorianDate)
                    ->where('status', 'registered')
                    ->exists();
                if ($duplicateExists) {
                    $result['skipped_rows']++;
                    continue;
                }
            }

            $groupKey = $gregorianDate . '|' . ($invoiceNumber !== '' ? $invoiceNumber : 'ROW_' . $excelRowNumber);

            if (!isset($groupedReceipts[$groupKey])) {
                $groupedReceipts[$groupKey] = [
                    'header' => [
                        'purchase_date' => $gregorianDate,
                        'invoice_number' => $invoiceNumber ?: null,
                        'warehouse_id' => $targetWarehouseId,
                        'supplier_name' => $supplierName ?: null,
                        'notes' => 'ثبت‌شده از درون‌ریزی اکسل',
                    ],
                    'lines' => [],
                ];
            }

            $groupedReceipts[$groupKey]['lines'][] = [
                'item_id' => $item->id,
                'warehouse_id' => $targetWarehouseId,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'expiry_date' => $expiryDate,
                'notes' => $notes ?: null,
            ];
        }

        if (empty($groupedReceipts)) {
            return $result;
        }

        DB::beginTransaction();
        try {
            foreach ($groupedReceipts as $group) {
                $receipt = $purchaseService->createReceipt($group['header'], $group['lines'], $updateCurrentPrices);
                $result['receipts_created']++;
                $result['lines_imported'] += $receipt->total_items_count;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $result['errors'][] = 'خطا در ثبت رسیدهای خرید از اکسل: ' . $e->getMessage();
        }

        return $result;
    }

    protected function mapPurchaseHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $colIdx => $text) {
            $t = trim(mb_strtolower((string)$text));
            if ($t === '') {
                continue;
            }

            if (mb_strpos($t, 'انقضا') !== false || mb_strpos($t, 'expiry') !== false) {
                $map['expiry_date'] = $colIdx;
            } elseif (mb_strpos($t, 'تاریخ') !== false || $t === 'date') {
                $map['date'] = $colIdx;
            } elseif (mb_strpos($t, 'فاکتور') !== false || mb_strpos($t, 'رسید') !== false || $t === 'invoice') {
                $map['invoice_number'] = $colIdx;
            } elseif (mb_strpos($t, 'کد کالا') !== false || $t === 'sku' || $t === 'code') {
                $map['code'] = $colIdx;
            } elseif (mb_strpos($t, 'نام کالا') !== false || $t === 'name') {
                $map['name'] = $colIdx;
            } elseif (mb_strpos($t, 'تعداد') !== false || $t === 'qty' || $t === 'quantity') {
                $map['quantity'] = $colIdx;
            } elseif ($t === 'فی' || mb_strpos($t, 'قیمت') !== false || mb_strpos($t, 'بهای خرید') !== false || $t === 'price') {
                $map['unit_price'] = $colIdx;
            } elseif (mb_strpos($t, 'انبار') !== false || $t === 'warehouse') {
                $map['warehouse'] = $colIdx;
            } elseif (mb_strpos($t, 'تأمین') !== false || mb_strpos($t, 'فروشنده') !== false || $t === 'supplier') {
                $map['supplier'] = $colIdx;
            } elseif (mb_strpos($t, 'توضیح') !== false || $t === 'notes') {
                $map['notes'] = $colIdx;
            }
        }
        return $map;
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
                    $colCursor = 0;
                    foreach ($row->c as $c) {
                        $cellType = isset($c['t']) ? (string)$c['t'] : '';
                        if ($cellType === 'inlineStr') {
                            $val = isset($c->is->t) ? (string)$c->is->t : '';
                        } else {
                            $val = isset($c->v) ? (string)$c->v : '';
                            if ($cellType === 's' && $val !== '') {
                                $sIdx = (int)$val;
                                $val = $sharedStrings[$sIdx] ?? $val;
                            }
                        }

                        $colIdx = $colCursor;
                        if (isset($c['r']) && preg_match('/^([A-Z]+)/i', (string)$c['r'], $colMatch)) {
                            $colIdx = $this->excelColLettersToIndex(strtoupper($colMatch[1]));
                        }
                        $rowVals[$colIdx] = $val;
                        $colCursor = $colIdx + 1;
                    }
                    if (!empty($rowVals)) {
                        $maxCol = max(array_keys($rowVals));
                        for ($k = 0; $k <= $maxCol; $k++) {
                            if (!isset($rowVals[$k])) {
                                $rowVals[$k] = '';
                            }
                        }
                        ksort($rowVals);
                    }
                    $sheetRows[] = $rowVals;
                }
            }

            $result[$sheetName] = $sheetRows;
        }

        $zip->close();
        return $result;
    }

    protected function excelColLettersToIndex(string $letters): int
    {
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }
}
