<?php

namespace Modules\FieldService\App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\FieldService\App\Models\ServiceOrder;

class ServiceOrderStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceOrder $order;
    public ?string $previousStatus;

    public function __construct(ServiceOrder $order, ?string $previousStatus = null)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orders.' . $this->order->id),
            new Channel('fieldservice.fleet'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ServiceOrderStatusChanged';
    }

    public function broadcastWith(): array
    {
        $tech = $this->order->technician;

        return [
            'order_id'          => $this->order->id,
            'order_number'      => $this->order->order_number,
            'previous_status'   => $this->previousStatus,
            'status'            => $this->order->status?->value ?? '',
            'status_label'      => $this->order->status?->label() ?? '',
            'badge_class'       => $this->order->status?->badgeClass() ?? '',
            'technician_id'     => $this->order->technician_id,
            'technician'        => $tech ? [
                'id'            => $tech->id,
                'full_name'     => $tech->full_name,
                'mobile'        => $tech->mobile,
                'avatar'        => $tech->avatar,
                'rating'        => (float) ($tech->rating ?? 5.0),
                'reviews_count' => (int) ($tech->reviews_count ?? 12),
                'vehicle_type'  => $tech->vehicle_type ?? 'موتورسیکلت تجهیزشده',
                'lat'           => (float) $tech->lat,
                'lng'           => (float) $tech->lng,
            ] : null,
            'lat'               => (float) $this->order->lat,
            'lng'               => (float) $this->order->lng,
            'customer_name'     => $this->order->customer_name,
            'address'           => $this->order->address,
            'accepted_at'       => $this->order->accepted_at?->toIso8601String(),
            'en_route_at'       => $this->order->en_route_at?->toIso8601String(),
            'arrived_at'        => $this->order->arrived_at?->toIso8601String(),
            'completed_at'      => $this->order->completed_at?->toIso8601String(),
            'timestamp'         => now()->toIso8601String(),
        ];
    }
}
