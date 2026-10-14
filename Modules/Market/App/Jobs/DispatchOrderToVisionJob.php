<?php

namespace Modules\Market\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Services\Vision\VisionOrderDispatchService;

class DispatchOrderToVisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30; // 30 seconds between retries

    public Order $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(VisionOrderDispatchService $dispatchService): void
    {
        Log::info("Dispatching order #{$this->order->id} to Vision ERP");

        $results = $dispatchService->dispatchOrder($this->order);

        foreach ($results as $vendorId => $res) {
            if (!$res['success']) {
                Log::warning("Failed to dispatch order #{$this->order->id} for vendor {$vendorId}: {$res['message']}");
            } else {
                Log::info("Successfully dispatched order #{$this->order->id} for vendor {$vendorId}. Factor: " . ($res['factor_number'] ?? 'N/A'));
            }
        }
    }
}
