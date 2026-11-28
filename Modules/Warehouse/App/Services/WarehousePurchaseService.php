<?php

namespace Modules\Warehouse\App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseItemBatch;
use Modules\Warehouse\Entities\WarehousePurchaseReceipt;
use Modules\Warehouse\Entities\WarehousePurchaseReceiptItem;
use Morilog\Jalali\Jalalian;

class WarehousePurchaseService
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
     * Create a multi-item purchase receipt and corresponding FIFO batches + stocks.
     */
    public function createReceipt(array $headerData, array $lines, bool $updateCurrentPrices = false): WarehousePurchaseReceipt
    {
        if (empty($lines)) {
            throw new \InvalidArgumentException('حداقل یک قلم کالا باید در رسید خرید ثبت شود.');
        }

        return DB::transaction(function () use ($headerData, $lines, $updateCurrentPrices) {
            $purchaseDate = $this->parseDate($headerData['purchase_date'] ?? null) ?? now()->toDateString();
            $defaultWarehouseId = !empty($headerData['warehouse_id']) ? (int)$headerData['warehouse_id'] : null;

            $receipt = WarehousePurchaseReceipt::create([
                'receipt_number' => !empty($headerData['receipt_number'])
                    ? trim((string)$headerData['receipt_number'])
                    : $this->codeService->generatePurchaseReceiptCode(),
                'invoice_number' => !empty($headerData['invoice_number']) ? trim((string)$headerData['invoice_number']) : null,
                'purchase_date' => $purchaseDate,
                'warehouse_id' => $defaultWarehouseId,
                'supplier_name' => !empty($headerData['supplier_name']) ? trim((string)$headerData['supplier_name']) : null,
                'update_current_prices' => $updateCurrentPrices,
                'status' => 'registered',
                'notes' => !empty($headerData['notes']) ? trim((string)$headerData['notes']) : null,
                'created_by' => $headerData['created_by'] ?? auth()->id(),
            ]);

            $this->attachLinesToReceipt($receipt, $lines, $updateCurrentPrices);

            return $receipt->fresh(['items.item', 'items.warehouse', 'items.batch', 'warehouse']);
        });
    }

    /**
     * Update an existing purchase receipt (only permitted if no linked batches were consumed).
     */
    public function updateReceipt(WarehousePurchaseReceipt $receipt, array $headerData, array $lines, bool $updateCurrentPrices = false): WarehousePurchaseReceipt
    {
        if (empty($lines)) {
            throw new \InvalidArgumentException('حداقل یک قلم کالا باید در رسید خرید ثبت شود.');
        }

        return DB::transaction(function () use ($receipt, $headerData, $lines, $updateCurrentPrices) {
            $lockedReceipt = WarehousePurchaseReceipt::where('id', $receipt->id)->lockForUpdate()->firstOrFail();

            if ($lockedReceipt->status === 'cancelled') {
                throw new \RuntimeException('رسید خرید ابطال‌شده قابل ویرایش نیست.');
            }

            // Reverse all existing batches safely (throws if any batch was partially or fully consumed)
            $existingItems = WarehousePurchaseReceiptItem::where('purchase_receipt_id', $lockedReceipt->id)->get();
            foreach ($existingItems as $oldLine) {
                if ($oldLine->batch) {
                    $this->valuationService->removeUnconsumedBatch($oldLine->batch);
                }
                $oldLine->delete();
            }

            $purchaseDate = $this->parseDate($headerData['purchase_date'] ?? null) ?? $lockedReceipt->purchase_date->toDateString();
            $defaultWarehouseId = !empty($headerData['warehouse_id']) ? (int)$headerData['warehouse_id'] : null;

            $lockedReceipt->update([
                'invoice_number' => !empty($headerData['invoice_number']) ? trim((string)$headerData['invoice_number']) : null,
                'purchase_date' => $purchaseDate,
                'warehouse_id' => $defaultWarehouseId,
                'supplier_name' => !empty($headerData['supplier_name']) ? trim((string)$headerData['supplier_name']) : null,
                'update_current_prices' => $updateCurrentPrices,
                'notes' => !empty($headerData['notes']) ? trim((string)$headerData['notes']) : null,
            ]);

            $this->attachLinesToReceipt($lockedReceipt, $lines, $updateCurrentPrices);

            return $lockedReceipt->fresh(['items.item', 'items.warehouse', 'items.batch', 'warehouse']);
        });
    }

    /**
     * Cancel / reverse a purchase receipt if none of its batches have been consumed.
     */
    public function cancelReceipt(WarehousePurchaseReceipt $receipt, bool $deleteRecord = false): void
    {
        DB::transaction(function () use ($receipt, $deleteRecord) {
            $lockedReceipt = WarehousePurchaseReceipt::where('id', $receipt->id)->lockForUpdate()->firstOrFail();

            if ($lockedReceipt->status === 'cancelled') {
                if ($deleteRecord) {
                    $lockedReceipt->delete();
                }
                return;
            }

            $existingItems = WarehousePurchaseReceiptItem::where('purchase_receipt_id', $lockedReceipt->id)->get();
            foreach ($existingItems as $line) {
                if ($line->batch) {
                    $this->valuationService->removeUnconsumedBatch($line->batch);
                }
            }

            $lockedReceipt->update([
                'status' => 'cancelled',
            ]);

            if ($deleteRecord) {
                $lockedReceipt->delete();
            }
        });
    }

    /**
     * Create receipt line items, batches, stocks, and update totals.
     */
    protected function attachLinesToReceipt(WarehousePurchaseReceipt $receipt, array $lines, bool $updateCurrentPrices): void
    {
        $fallbackWarehouseId = $receipt->warehouse_id
            ?: Warehouse::inventory()->main()->active()->value('id')
            ?: Warehouse::inventory()->active()->value('id');

        $totalQty = 0.0;
        $totalAmount = 0.0;
        $itemsCount = 0;

        foreach ($lines as $index => $line) {
            $itemId = (int)($line['item_id'] ?? 0);
            $quantity = (float)($line['quantity'] ?? 0);
            $unitPrice = (float)($line['unit_price'] ?? 0);

            if ($itemId <= 0 || $quantity <= 0) {
                continue;
            }

            $item = WarehouseItem::findOrFail($itemId);
            $targetWarehouseId = !empty($line['warehouse_id'])
                ? (int)$line['warehouse_id']
                : ($receipt->warehouse_id ?: ($item->default_warehouse_id ?: $fallbackWarehouseId));

            if (!$targetWarehouseId) {
                throw new \RuntimeException('هیچ انبار فعالی برای ورود کالا در سیستم یافت نشد.');
            }

            $expiryDate = $this->parseDate($line['expiry_date'] ?? null);
            $lineNotes = !empty($line['notes']) ? trim((string)$line['notes']) : null;

            $batchCode = $receipt->invoice_number
                ? ('INV-' . $receipt->invoice_number . (count($lines) > 1 ? '-' . ($index + 1) : ''))
                : ($receipt->receipt_number . (count($lines) > 1 ? '-' . ($index + 1) : ''));

            $batch = $this->valuationService->addBatch(
                $item,
                $targetWarehouseId,
                $quantity,
                $unitPrice,
                $batchCode,
                $receipt->purchase_date->toDateString(),
                $expiryDate,
                $receipt->supplier_name,
                $lineNotes ?: ('ورود با رسید ' . $receipt->receipt_number),
                $receipt->id
            );

            if ($updateCurrentPrices && $unitPrice > 0) {
                $item->updateQuietly([
                    'current_price' => $unitPrice,
                ]);
            }

            $lineTotal = round($quantity * $unitPrice, 2);

            WarehousePurchaseReceiptItem::create([
                'purchase_receipt_id' => $receipt->id,
                'item_id' => $item->id,
                'warehouse_id' => $targetWarehouseId,
                'batch_id' => $batch->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
                'expiry_date' => $expiryDate,
                'notes' => $lineNotes,
            ]);

            $totalQty += $quantity;
            $totalAmount += $lineTotal;
            $itemsCount++;
        }

        if ($itemsCount === 0) {
            throw new \InvalidArgumentException('هیچ ردیف معتبری با تعداد بیشتر از صفر جهت ثبت وجود نداشت.');
        }

        $receipt->update([
            'total_items_count' => $itemsCount,
            'total_quantity' => $totalQty,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Parse Jalali (1405/09/03), Gregorian (2026-11-24), or Excel serial dates into Y-m-d string.
     */
    public function parseDate(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $clean = trim($this->normalizeDigits($input));
        if ($clean === '') {
            return null;
        }

        // Handle Excel numeric date serial (e.g. 46000)
        if (is_numeric($clean) && (float)$clean > 20000 && (float)$clean < 80000) {
            $unixTimestamp = ((float)$clean - 25569) * 86400;
            return Carbon::createFromTimestampUTC((int)$unixTimestamp)->toDateString();
        }

        $normalized = str_replace(['-', '.'], '/', $clean);
        if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $normalized, $m)) {
            $year = (int)$m[1];
            $month = (int)$m[2];
            $day = (int)$m[3];

            if ($year >= 1300 && $year <= 1550) {
                return Jalalian::fromFormat('Y/n/j', "{$year}/{$month}/{$day}")->toCarbon()->toDateString();
            }

            if ($year >= 1900 && $year <= 2200) {
                return Carbon::createFromDate($year, $month, $day)->toDateString();
            }
        }

        try {
            return Carbon::parse($clean)->toDateString();
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("فرمت تاریخ «{$input}» معتبر نیست. لطفاً از فرمت صحیح مثل 1405/09/03 استفاده کنید.");
        }
    }

    public function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
