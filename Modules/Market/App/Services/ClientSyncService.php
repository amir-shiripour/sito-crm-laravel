<?php

namespace Modules\Market\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Clients\Entities\Client;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Models\OrderSyncLog;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\App\Models\CheckoutForm;

class ClientSyncService
{
    public function hydrate(Client $client, string $formKey): array
    {
        $data = [];
        if (!MarketSetting::getValue('checkout.auto_fill_from_client', true)) {
            return $data;
        }

        $form = CheckoutForm::where('key', $formKey)->first();
        if (!$form) {
            return $data;
        }

        $schema = $form->getSchema();

        foreach ($schema['fields'] as $field) {
            $fieldId = $field['id'];
            $value = null;
            $hasSource = isset($field['source']) && str_starts_with($field['source'], 'client.');

            // Attempt 1: Use the 'source' attribute if it exists
            if ($hasSource) {
                $sourceKey = substr($field['source'], 7);
                $value = $client->{$sourceKey} ?? $client->meta[$sourceKey] ?? null;
            }

            // Attempt 2: Fallback to direct ID matching if the first attempt yielded no value
            if (is_null($value)) {
                $fallbackValue = $client->{$fieldId} ?? $client->meta[$fieldId] ?? null;
                if (!is_null($fallbackValue)) {
                    $value = $fallbackValue;
                }
            }

            // Final assignment
            $data[$fieldId] = $value;
        }

        return $data;
    }

    public function sync(Order $order, Client $client): void
    {
        DB::transaction(function () use ($order, $client) {
            $form = $order->checkoutForm;
            if (!$form) {
                return;
            }

            $orderMeta = $order->meta->pluck('value', 'key')->all();
            $schema = $form->getSchema();

            foreach ($schema['fields'] as $field) {
                if (empty($field['sync']) || !isset($orderMeta[$field['id']])) {
                    continue;
                }

                $newValue = $orderMeta[$field['id']];
                $syncMode = $field['sync'];
                $sourceKey = isset($field['client_sync_key']) ? $field['client_sync_key'] : ($field['id'] ?? null);

                if (!$sourceKey) {
                    continue;
                }

                $isMeta = !in_array($sourceKey, array_keys(Client::SYSTEM_FIELDS));
                $oldValue = $isMeta ? ($client->meta[$sourceKey] ?? null) : $client->{$sourceKey};

                if ($newValue == $oldValue) {
                    continue;
                }

                $requiresApproval = MarketSetting::getValue('checkout.sync_require_approval', false);
                $isUpdate = !is_null($oldValue);

                $shouldUpdate = ($syncMode === 'always_update') || ($syncMode === 'fill_if_empty' && !$isUpdate);

                if (!$shouldUpdate) {
                    continue;
                }

                $status = ($requiresApproval && $isUpdate) ? 'pending' : 'auto_applied';

                if ($status === 'auto_applied') {
                    if ($isMeta) {
                        $meta = $client->meta;
                        $meta[$sourceKey] = $newValue;
                        $client->meta = $meta;
                    } else {
                        $client->{$sourceKey} = $newValue;
                    }
                    $client->save();
                }

                OrderSyncLog::create([
                    'order_id' => $order->id,
                    'client_id' => $client->id,
                    'field_key' => $sourceKey,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'source' => 'checkout',
                    'status' => $status,
                ]);
            }
        });
    }

    public function approveLog(int $logId, int $userId): void
    {
        DB::transaction(function () use ($logId, $userId) {
            $log = OrderSyncLog::findOrFail($logId);
            if ($log->status !== 'pending') {
                return;
            }

            $client = $log->client;
            $isMeta = !in_array($log->field_key, array_keys(Client::SYSTEM_FIELDS));

            if ($isMeta) {
                $meta = $client->meta;
                $meta[$log->field_key] = $log->new_value;
                $client->meta = $meta;
            } else {
                $client->{$log->field_key} = $log->new_value;
            }

            $client->save();

            $log->update([
                'status' => 'approved',
                'reviewed_by' => $userId,
                'reviewed_at' => now(),
            ]);
        });
    }

    public function rejectLog(int $logId, int $userId): void
    {
        $log = OrderSyncLog::findOrFail($logId);
        if ($log->status !== 'pending') {
            return;
        }

        $log->update([
            'status' => 'rejected',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
        ]);
    }
}
