<?php

namespace Modules\Clients\App\Observers;

use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientStatus;

class ClientObserver
{
    public function created(Client $client): void
    {
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            /** @var \Modules\Workflows\Services\WorkflowEngine $engine */
            $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
            $newStatus = $client->status;
            $engine->start(
                'client_created',
                'CLIENT',
                $client->id,
                [
                    'client' => $client,
                    'previous_status' => null,
                    'current_status' => $newStatus?->key,
                ]
            );
        }
    }

    public function updated(Client $client): void
    {
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            /** @var \Modules\Workflows\Services\WorkflowEngine $engine */
            $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);

            if ($client->isDirty('status_id')) {
                $originalStatusId = $client->getOriginal('status_id');
                $previousStatus = null;
                if ($originalStatusId) {
                    $origStatus = ClientStatus::find($originalStatusId);
                    $previousStatus = $origStatus?->key;
                }

                $newStatus = $client->status;
                $currentStatus = $newStatus?->key;

                $payload = [
                    'client' => $client,
                    'previous_status' => $previousStatus,
                    'current_status' => $currentStatus,
                ];

                // Trigger general status changed event
                $engine->start(
                    'client_status_changed',
                    'CLIENT',
                    $client->id,
                    $payload
                );
            } else {
                $newStatus = $client->status;
                // Trigger general client updated event
                $engine->start(
                    'client_updated',
                    'CLIENT',
                    $client->id,
                    [
                        'client' => $client,
                        'previous_status' => null,
                        'current_status' => $newStatus?->key,
                    ]
                );
            }
        }
    }
}
