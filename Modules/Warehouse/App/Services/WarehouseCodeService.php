<?php

namespace Modules\Warehouse\App\Services;

use Modules\Warehouse\Entities\WarehouseItem;

class WarehouseCodeService
{
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
