<?php

namespace Modules\FieldService\App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TechnicianLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $technicianId;
    public ?int $orderId;
    public float $lat;
    public float $lng;
    public ?float $bearing;
    public ?float $speed;

    public function __construct(int $technicianId, ?int $orderId, float $lat, float $lng, ?float $bearing = null, ?float $speed = null)
    {
        $this->technicianId = $technicianId;
        $this->orderId = $orderId;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->bearing = $bearing;
        $this->speed = $speed;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('fieldservice.fleet'),
        ];

        if ($this->orderId) {
            $channels[] = new Channel('orders.' . $this->orderId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'TechnicianLocationUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'technician_id' => $this->technicianId,
            'order_id'      => $this->orderId,
            'lat'           => $this->lat,
            'lng'           => $this->lng,
            'bearing'       => $this->bearing,
            'speed'         => $this->speed,
            'timestamp'     => now()->toIso8601String(),
        ];
    }
}
