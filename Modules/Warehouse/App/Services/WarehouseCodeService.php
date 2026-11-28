<?php

namespace Modules\Warehouse\App\Services;

use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseIssueDocument;
use Modules\Warehouse\Entities\WarehousePurchaseReceipt;
use Modules\Warehouse\Entities\WarehouseTransferRequest;

class WarehouseCodeService
{
    /**
     * Generate unique transfer request number (e.g. WH-TRN-100001)
     */
    public function generateTransferRequestCode(string $prefix = 'WH-TRN-'): string
    {
        return $this->generateSequentialTransferCode('transfer_number', $prefix);
    }

    /**
     * Generate unique system request number (e.g. WH-REQ-100001)
     */
    public function generateRequestNumber(string $prefix = 'WH-REQ-'): string
    {
        return $this->generateSequentialTransferCode('request_number', $prefix);
    }

    protected function generateSequentialTransferCode(string $column, string $prefix): string
    {
        $latest = WarehouseTransferRequest::withTrashed()
            ->where($column, 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $counter = 100001;
        if ($latest && preg_match('/(\d+)$/', (string)$latest->{$column}, $matches)) {
            $counter = max($counter, (int)$matches[1] + 1);
        }

        $code = $prefix . $counter;
        while (WarehouseTransferRequest::withTrashed()->where($column, $code)->exists()) {
            $counter++;
            $code = $prefix . $counter;
        }

        return $code;
    }

    /**
     * Generate unique goods issue / consumption document number (e.g. WH-OUT-100001)
     */
    public function generateIssueNumber(string $prefix = 'WH-OUT-'): string
    {
        $latest = WarehouseIssueDocument::withTrashed()
            ->where('issue_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $counter = 100001;
        if ($latest && preg_match('/(\d+)$/', (string)$latest->issue_number, $matches)) {
            $counter = max($counter, (int)$matches[1] + 1);
        }

        $code = $prefix . $counter;
        while (WarehouseIssueDocument::withTrashed()->where('issue_number', $code)->exists()) {
            $counter++;
            $code = $prefix . $counter;
        }

        return $code;
    }

    /**
     * Generate unique purchase receipt number (e.g. WH-PUR-100001)
     */
    public function generatePurchaseReceiptCode(string $prefix = 'WH-PUR-'): string
    {
        $latest = WarehousePurchaseReceipt::withTrashed()
            ->where('receipt_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $counter = 100001;
        if ($latest && preg_match('/(\d+)$/', $latest->receipt_number, $matches)) {
            $counter = max($counter, (int)$matches[1] + 1);
        }

        $code = $prefix . $counter;
        while (WarehousePurchaseReceipt::withTrashed()->where('receipt_number', $code)->exists()) {
            $counter++;
            $code = $prefix . $counter;
        }

        return $code;
    }

    /**
     * Generate unique system code for item (e.g. WH-INV-100001)
     */
    public function generateSystemCode(string $prefix = 'WH-INV-'): string
    {
        $latest = WarehouseItem::where('system_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $counter = 100001;
        if ($latest && preg_match('/(\d+)$/', $latest->system_code, $matches)) {
            $counter = max($counter, (int)$matches[1] + 1);
        }

        $code = $prefix . $counter;
        while (WarehouseItem::where('system_code', $code)->exists()) {
            $counter++;
            $code = $prefix . $counter;
        }

        return $code;
    }

    /**
     * Compose suggested SKU/commercial code based on formula:
     * شناسه اختصاری دسته + "-" + "شناسه اختصاری زیردسته" + "-" + "شناسه اختصاری جنس" + "-" + "شناسه اختصاری برند" + "-" + سایز
     */
    public function generateSuggestedSku(
        ?string $categoryCode = null,
        ?string $subcategoryCode = null,
        ?string $itemTypeCode = null,
        ?string $brandCode = null,
        ?string $spec = null
    ): string {
        $parts = [];

        if (!empty($categoryCode)) {
            $parts[] = trim($categoryCode);
        }

        if (!empty($subcategoryCode)) {
            $parts[] = trim($subcategoryCode);
        }

        if (!empty($itemTypeCode)) {
            $parts[] = trim($itemTypeCode);
        }

        if (!empty($brandCode)) {
            $parts[] = trim($brandCode);
        }

        if (!empty($spec)) {
            $cleanSpec = trim(preg_replace('/\s*x\s*/i', 'x', $spec));
            $parts[] = $cleanSpec;
        }

        return strtoupper(implode('-', array_filter($parts)));
    }

    /**
     * Compose suggested Persian product name based on formula:
     * "عنوان  زیردسته" + "عنوان  جنس" + "عنوان برند" + سایز
     */
    public function generateSuggestedName(
        ?string $subcategory = null,
        ?string $itemType = null,
        ?string $brand = null,
        ?string $spec = null
    ): string {
        $parts = [];

        if (!empty($subcategory)) {
            $parts[] = trim($subcategory);
        }

        if (!empty($itemType)) {
            $parts[] = trim($itemType);
        }

        if (!empty($brand)) {
            $parts[] = trim($brand);
        }

        if (!empty($spec)) {
            $parts[] = trim($spec);
        }

        return implode(' ', array_filter($parts));
    }
}
