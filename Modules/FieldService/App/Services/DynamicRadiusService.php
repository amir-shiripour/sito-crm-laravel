<?php

namespace Modules\FieldService\App\Services;

use Illuminate\Support\Facades\Log;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\ServiceOrder;

class DynamicRadiusService
{
    /**
     * Expand radius to next stage and re-attempt dispatching.
     */
    public function expandRadiusAndRetry(ServiceOrder $order): array
    {
        $steps = config('fieldservice.radius_expansion_km', [3, 7, 15, 30, 60]);
        $currentRadius = $order->search_radius_km ?: 3;

        $nextRadius = null;
        foreach ($steps as $step) {
            if ($step > $currentRadius) {
                $nextRadius = $step;
                break;
            }
        }

        if (!$nextRadius) {
            Log::warning("[DynamicRadius] Maximum provincial search radius reached (60km) for Order #{$order->order_number}.");
            $order->status = OrderStatus::SEARCHING;
            $order->save();

            return [
                'success'           => false,
                'message'           => 'در حال حاضر در کل استان تکنسین مجازی یافت نشد؛ اپراتور مرکزی در حال پیگیری است.',
                'technicians_count' => 0,
                'radius_km'         => $currentRadius,
            ];
        }

        $order->search_radius_km = $nextRadius;
        $order->search_expanded_count = ($order->search_expanded_count ?: 0) + 1;
        $order->status = OrderStatus::SEARCHING;
        $order->save();

        Log::info("[DynamicRadius] Order #{$order->order_number} expanded radius to {$nextRadius}km (Expansion #{$order->search_expanded_count}).");

        /** @var DispatchRadarService $radar */
        $radar = app(DispatchRadarService::class);
        return $radar->dispatchOrder($order);
    }
}
