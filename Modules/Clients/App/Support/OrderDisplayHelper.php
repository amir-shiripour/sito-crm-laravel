<?php

declare(strict_types=1);

namespace Modules\Clients\App\Support;

use Modules\Services\App\Http\Models\Order;

final class OrderDisplayHelper
{
    /**
     * Resolve the appropriate title/name for a service order.
     * Supports both predefined catalog services and custom manual line items.
     */
    public static function getTitle(?Order $order, string $fallback = 'سفارش خدمات'): string
    {
        if (!$order) {
            return $fallback;
        }

        // 1. If service relationship is set and has a name
        if ($order->service && !empty(trim((string)$order->service->name))) {
            return trim((string)$order->service->name);
        }

        // 2. If invoice is loaded, inspect invoice items for custom service name or description
        if ($order->relationLoaded('invoice') && $order->invoice && $order->invoice->relationLoaded('items')) {
            $item = null;
            if (!empty($order->notes)) {
                $item = $order->invoice->items->first(function ($it) use ($order) {
                    return $it->custom_service_name === $order->notes
                        || $it->description === $order->notes;
                });
            }

            if (!$item) {
                // Find first manual item in invoice
                $item = $order->invoice->items->first(function ($it) {
                    return empty($it->service_id);
                });
            }

            if ($item) {
                if (!empty(trim((string)$item->custom_service_name))) {
                    return trim((string)$item->custom_service_name);
                }
                if (!empty(trim((string)$item->description))) {
                    return trim((string)$item->description);
                }
            }
        }

        // 3. Fallback to order notes if present
        if (!empty(trim((string)$order->notes))) {
            $notes = trim((string)$order->notes);
            if (str_contains($notes, 'سرویس:')) {
                if (preg_match('/سرویس:\s*([^|]+)/u', $notes, $matches)) {
                    $extracted = trim($matches[1]);
                    if (!empty($extracted)) {
                        return $extracted;
                    }
                }
            }
            return $notes;
        }

        return $fallback;
    }
}