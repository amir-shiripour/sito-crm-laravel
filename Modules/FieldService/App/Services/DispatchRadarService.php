<?php

namespace Modules\FieldService\App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;

class DispatchRadarService
{
    protected DynamicRadiusService $radiusService;
    protected FieldServiceWorkflowBridgeService $workflowBridge;

    public function __construct(
        DynamicRadiusService $radiusService,
        FieldServiceWorkflowBridgeService $workflowBridge
    ) {
        $this->radiusService  = $radiusService;
        $this->workflowBridge = $workflowBridge;
    }

    /**
     * Scan and find eligible online technicians within current order radius.
     *
     * @return Collection<int, Technician>
     */
    public function findEligibleTechnicians(ServiceOrder $order): Collection
    {
        $radiusKm = $order->search_radius_km ?: 3;
        $orderLat = (float) $order->lat;
        $orderLng = (float) $order->lng;

        // Query active, online and available technicians
        $query = Technician::query()
            ->where('status', TechnicianStatus::ACTIVE)
            ->where('is_online', true)
            ->where('is_busy', false);

        // Filter by category skill if order has a specific category
        if ($order->category_id) {
            $query->whereHas('skills', function ($q) use ($order) {
                $q->where('category_id', $order->category_id);
            });
        }

        $allOnline = $query->get();

        // If order doesn't have lat/lng, return all online matching city
        if (!$orderLat || !$orderLng) {
            return $allOnline->filter(fn (Technician $t) => $t->city === $order->city);
        }

        // Filter strictly by distance (Haversine)
        return $allOnline
            ->map(function (Technician $tech) use ($orderLat, $orderLng) {
                $tech->distance_km = $tech->distanceTo($orderLat, $orderLng);
                return $tech;
            })
            ->filter(function (Technician $tech) use ($radiusKm) {
                return $tech->distance_km !== null && $tech->distance_km <= $radiusKm;
            })
            ->sortBy('distance_km')
            ->values();
    }

    /**
     * Dispatch mission invitation to eligible technicians.
     */
    public function dispatchOrder(ServiceOrder $order): array
    {
        $eligible = $this->findEligibleTechnicians($order);

        if ($eligible->isEmpty()) {
            Log::info("[DispatchRadar] No technician found in radius {$order->search_radius_km}km for Order #{$order->order_number}. Expanding radius...");
            return $this->radiusService->expandRadiusAndRetry($order);
        }

        $order->status = OrderStatus::DISPATCHED;
        $order->dispatched_at = now();
        $order->dispatch_timeout_seconds = (int) config('fieldservice.dispatch_timeout_seconds', 45);
        $order->save();

        Log::info("[DispatchRadar] Order #{$order->order_number} dispatched to {$eligible->count()} technicians within {$order->search_radius_km}km.");

        // Trigger Workflow engine integration
        $this->workflowBridge->triggerOrderDispatched($order, $eligible);

        return [
            'success'           => true,
            'technicians_count' => $eligible->count(),
            'radius_km'         => $order->search_radius_km,
            'technicians'       => $eligible,
        ];
    }

    /**
     * Technician accepts mission.
     */
    public function acceptOrder(ServiceOrder $order, Technician $technician): bool
    {
        if (!$order->canAccept()) {
            return false;
        }

        $order->technician_id = $technician->id;
        $order->status = OrderStatus::ACCEPTED;
        $order->accepted_at = now();
        $order->save();

        $technician->is_busy = true;
        $technician->save();

        Log::info("[DispatchRadar] Technician {$technician->full_name} accepted Order #{$order->order_number}.");

        // Trigger Workflow engine integration
        $this->workflowBridge->triggerOrderAccepted($order, $technician);

        return true;
    }
}
