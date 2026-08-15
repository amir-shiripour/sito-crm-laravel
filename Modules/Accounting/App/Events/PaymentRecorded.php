<?php

namespace Modules\Accounting\App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class PaymentRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Model $documentable;
    public float $amount;
    public int $fundAccountId;
    public string $description;

    /**
     * Create a new event instance.
     */
    public function __construct(Model $documentable, float $amount, int $fundAccountId, string $description = '')
    {
        $this->documentable = $documentable;
        $this->amount = $amount;
        $this->fundAccountId = $fundAccountId;
        $this->description = $description;
    }
}
