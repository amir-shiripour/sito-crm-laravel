<?php

namespace Modules\FieldService\App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\FieldService\App\Models\ServiceOrder;

class ServiceOrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceOrder $order;

    public function __construct(ServiceOrder $order)
    {
        $this->order = $order;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('fieldservice.fleet'),
        ];

        if (!empty($this->order->city)) {
            $channels[] = new Channel('fieldservice.city.' . urlencode($this->order->city));
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'ServiceOrderCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'             => $this->order->id,
            'order_number'         => $this->order->order_number,
            'status'               => $this->order->status?->value ?? 'pending',
            'status_label'         => $this->order->status?->label() ?? 'ثبت شده',
            'badge_class'          => $this->order->status?->badgeClass() ?? '',
            'category_id'          => $this->order->category_id,
            'appliance_type'       => $this->order->appliance_type?->value,
            'appliance_label'      => $this->order->appliance_type?->label(),
            'appliance_brand_model'=> $this->order->appliance_brand_model,
            'problem_description'  => $this->order->problem_description,
            'is_emergency'         => (bool) $this->order->is_emergency,
            'lat'                  => (float) $this->order->lat,
            'lng'                  => (float) $this->order->lng,
            'province'             => $this->order->province,
            'city'                 => $this->order->city,
            'district'             => $this->order->district,
            'address'              => $this->order->address,
            'customer_name'        => $this->order->customer_name,
            'customer_phone'       => $this->order->customer_phone,
            'created_at'           => $this->order->created_at?->toIso8601String(),
        ];
    }
}
