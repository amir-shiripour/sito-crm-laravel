<?php

namespace Modules\ClientCalls\App\Observers;

use Modules\ClientCalls\Entities\ClientCall;

class ClientCallObserver
{
    /**
     * Handle the ClientCall "created" event.
     */
    public function created(ClientCall $call): void
    {
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
            $engine->start(
                'call_created',
                'CLIENT_CALL',
                $call->id,
                [
                    'call' => $call,
                    'client' => $call->client,
                    'previous_status' => null,
                    'current_status' => $call->status,
                ]
            );
        }
    }

    /**
     * Handle the ClientCall "updated" event.
     */
    public function updated(ClientCall $call): void
    {
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);

            if ($call->isDirty('status')) {
                $payload = [
                    'call' => $call,
                    'client' => $call->client,
                    'previous_status' => $call->getOriginal('status'),
                    'current_status' => $call->status,
                ];

                $engine->start(
                    'call_status_changed',
                    'CLIENT_CALL',
                    $call->id,
                    $payload
                );
            } else {
                $engine->start(
                    'call_updated',
                    'CLIENT_CALL',
                    $call->id,
                    [
                        'call' => $call,
                        'client' => $call->client,
                        'previous_status' => null,
                        'current_status' => $call->status,
                    ]
                );
            }
        }
    }
}
