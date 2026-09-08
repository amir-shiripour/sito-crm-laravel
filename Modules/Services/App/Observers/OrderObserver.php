<?php

namespace Modules\Services\App\Observers;

use Illuminate\Support\Facades\Log;
use Modules\Services\App\Http\Models\Order;
use Throwable;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Only run if the status_id was changed
        if (!$order->wasChanged('status_id')) {
            return;
        }

        // Check if DirectAdmin module is present and enabled
        if (!Order::isDirectAdminActive()) {
            return;
        }

        try {
            // Check if two-way sync setting is enabled
            $autoSync = \Modules\DirectAdmin\Entities\DaSetting::get('auto_sync_order_status', true);
            if (!$autoSync) {
                return;
            }

            // Load hosting account
            $hostingAccount = $order->hostingAccount;
            if (!$hostingAccount || !$hostingAccount->server) {
                return;
            }

            $order->loadMissing('status');
            $statusName = $order->status?->name ?? '';

            $client = new \Modules\DirectAdmin\Services\DirectAdminClient($hostingAccount->server);

            if (str_contains($statusName, 'غیر') || str_contains($statusName, 'لغو') || str_contains($statusName, 'معلق')) {
                if (!$hostingAccount->suspended) {
                    $result = $client->suspendUser($hostingAccount->username, 'مسدودسازی خودکار به علت تغییر وضعیت سفارش به غیرفعال/لغو');
                    if ($result['success'] ?? false) {
                        $hostingAccount->update([
                            'suspended' => true,
                            'status' => 'suspended',
                        ]);
                    }
                }
            }
            // Active condition
            elseif ($statusName === 'فعال' || (str_contains($statusName, 'فعال') && !str_contains($statusName, 'غیر'))) {
                if ($hostingAccount->suspended) {
                    $result = $client->unsuspendUser($hostingAccount->username);
                    if ($result['success'] ?? false) {
                        $hostingAccount->update([
                            'suspended' => false,
                            'status' => 'active',
                        ]);
                    }
                }
            }
        } catch (Throwable $e) {
            Log::error("DirectAdmin auto-sync failed for order #{$order->id}: " . $e->getMessage());
        }
    }
}
