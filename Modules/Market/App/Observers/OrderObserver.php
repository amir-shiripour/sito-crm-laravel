<?php

namespace Modules\Market\App\Observers;

use Modules\Market\App\Jobs\DispatchOrderToVisionJob;
use Modules\Market\App\Models\Order;
use Modules\Market\Entities\VendorVisionSetting;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if payment_status changed to 'paid'
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $this->checkAndDispatchVision($order);
        }
    }

    /**
     * Check if any vendor in order has active Vision ERP with auto_dispatch enabled
     */
    protected function checkAndDispatchVision(Order $order): void
    {
        try {
            $vendorIds = $order->items()->pluck('vendor_id')->unique()->filter();

            $hasActiveVision = VendorVisionSetting::whereIn('vendor_id', $vendorIds)
                ->where('is_active', true)
                ->where('auto_dispatch_order', true)
                ->exists();

            if ($hasActiveVision) {
                dispatch(new DispatchOrderToVisionJob($order));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OrderObserver Vision Dispatch Check Error: ' . $e->getMessage());
        }
    }
}
