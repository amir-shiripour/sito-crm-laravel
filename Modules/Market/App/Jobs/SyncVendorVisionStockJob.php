<?php

namespace Modules\Market\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Market\App\Services\Vision\VisionStockSyncService;
use Modules\Market\Entities\VendorVisionSetting;

class SyncVendorVisionStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $vendorId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $vendorId)
    {
        $this->vendorId = $vendorId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $setting = VendorVisionSetting::where('vendor_id', $this->vendorId)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            return;
        }

        $stockService = new VisionStockSyncService($setting);
        $result = $stockService->syncStock();

        if ($result['success']) {
            Log::info("Vision stock sync finished for vendor {$this->vendorId}. Updated items: {$result['updated_count']}");
        } else {
            Log::warning("Vision stock sync failed for vendor {$this->vendorId}: {$result['message']}");
        }
    }
}
