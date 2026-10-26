<?php

namespace Modules\FieldService\App\Observers;

use Illuminate\Support\Facades\Log;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Events\ServiceOrderCreated;
use Modules\FieldService\App\Events\ServiceOrderStatusChanged;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;
use Modules\Workflows\Services\WorkflowEngine;

class ServiceOrderObserver
{
    protected ?WorkflowEngine $workflowEngine = null;
    protected ?FieldServiceWorkflowBridgeService $bridge = null;

    public function __construct()
    {
        if (class_exists(WorkflowEngine::class)) {
            $this->workflowEngine = app(WorkflowEngine::class);
        }
        if (class_exists(FieldServiceWorkflowBridgeService::class)) {
            $this->bridge = app(FieldServiceWorkflowBridgeService::class);
        }
    }

    /**
     * Handle the ServiceOrder "created" event.
     */
    public function created(ServiceOrder $order): void
    {
        try {
            // 1. Workflow Engine Integration
            if ($this->workflowEngine) {
                $eventKey = $order->is_emergency ? 'fieldservice_emergency_created' : 'fieldservice_order_created';
                $this->workflowEngine->start(
                    $eventKey,
                    'FIELD_SERVICE_ORDER',
                    $order->id,
                    [
                        'order'          => $order,
                        'order_id'       => $order->id,
                        'order_number'   => $order->order_number,
                        'customer_name'  => $order->customer_name,
                        'customer_phone' => $order->customer_phone,
                        'city'           => $order->city,
                        'address'        => $order->address,
                        'is_emergency'   => (bool) $order->is_emergency,
                        'status'         => $order->status?->value ?? 'pending',
                    ]
                );
            }

            // 2. FieldService Bridge Notification
            if ($this->bridge) {
                $this->bridge->triggerOrderCreated($order);
            }

            // 3. Real-Time WebSocket Broadcast to Fleet (Reverb/Echo)
            event(new ServiceOrderCreated($order));

            Log::info("[FieldService] ServiceOrderObserver: created event dispatched for Order #{$order->order_number}");
        } catch (\Throwable $e) {
            Log::error("[FieldService] ServiceOrderObserver created error: " . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * Handle the ServiceOrder "updated" event.
     */
    public function updated(ServiceOrder $order): void
    {
        try {
            $statusChanged = $order->isDirty('status');
            $technicianChanged = $order->isDirty('technician_id');

            if ($statusChanged) {
                $rawPrev = $order->getOriginal('status');
                $previousStatus = $rawPrev instanceof OrderStatus ? $rawPrev->value : (string) $rawPrev;
                $newStatus = $order->status instanceof OrderStatus ? $order->status->value : (string) $order->status;

                Log::info("[FieldService] ServiceOrderObserver: status changed for Order #{$order->order_number} from '{$previousStatus}' to '{$newStatus}'");

                // 1. Trigger Workflow Engine
                if ($this->workflowEngine) {
                    $workflowKey = 'fieldservice_order_' . strtolower($newStatus);
                    $this->workflowEngine->start(
                        $workflowKey,
                        'FIELD_SERVICE_ORDER',
                        $order->id,
                        [
                            'order'           => $order,
                            'order_id'        => $order->id,
                            'order_number'    => $order->order_number,
                            'previous_status' => $previousStatus,
                            'new_status'      => $newStatus,
                            'technician_id'   => $order->technician_id,
                            'customer_phone'  => $order->customer_phone,
                            'customer_name'   => $order->customer_name,
                        ]
                    );
                }

                // 2. Trigger Bridge Methods based on status
                if ($this->bridge) {
                    if ($newStatus === OrderStatus::ACCEPTED->value && $order->technician) {
                        $this->bridge->triggerOrderAccepted($order, $order->technician);
                    } elseif ($newStatus === OrderStatus::ARRIVED->value) {
                        $this->bridge->triggerTechnicianArrived($order);
                    } elseif ($newStatus === OrderStatus::COMPLETED->value) {
                        $this->bridge->triggerOrderCompleted($order);
                    }
                }

                // Auto-free technician if order was cancelled or completed
                if (in_array($newStatus, [OrderStatus::CANCELLED->value, OrderStatus::COMPLETED->value], true)) {
                    if ($order->technician_id) {
                        \Modules\FieldService\App\Models\Technician::where('id', $order->technician_id)->update(['is_busy' => false]);
                    }
                }

                // 3. Broadcast Real-time Status Event (Reverb/Echo)
                event(new ServiceOrderStatusChanged($order, $previousStatus));
            } elseif ($technicianChanged) {
                // If technician changed without status changing
                event(new ServiceOrderStatusChanged($order, $order->status?->value));
            }
        } catch (\Throwable $e) {
            Log::error("[FieldService] ServiceOrderObserver updated error: " . $e->getMessage(), ['exception' => $e]);
        }
    }
}
