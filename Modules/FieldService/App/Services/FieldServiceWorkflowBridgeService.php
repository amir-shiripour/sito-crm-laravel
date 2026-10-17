<?php

namespace Modules\FieldService\App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\FieldService\App\Models\BackupRequest;
use Modules\FieldService\App\Models\ServiceInvoice;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\Workflows\Services\WorkflowEngine;

class FieldServiceWorkflowBridgeService
{
    /**
     * Safely dispatch an event to the CRM WorkflowEngine.
     */
    protected function dispatchToWorkflow(string $eventKey, string $relatedType, int $relatedId, array $payload = []): void
    {
        try {
            if (!class_exists(WorkflowEngine::class)) {
                return;
            }

            Log::info("[FieldService Workflows] Triggering workflow event: '{$eventKey}' for {$relatedType}:{$relatedId}");
            app(WorkflowEngine::class)->start($eventKey, $relatedType, $relatedId, $payload);
        } catch (\Throwable $e) {
            Log::error("[FieldService Workflows] Failed to start workflow for event '{$eventKey}': " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function triggerOrderCreated(ServiceOrder $order): void
    {
        $payload = [
            'order_id'       => $order->id,
            'order_number'   => $order->order_number,
            'client_id'      => $order->client_id,
            'customer_name'  => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'appliance_type' => $order->appliance_type?->value,
            'city'           => $order->city,
            'address'        => $order->address,
            'is_emergency'   => $order->is_emergency,
        ];

        $eventKey = $order->is_emergency 
            ? config('fieldservice.workflow_events.emergency_dispatched', 'fieldservice.emergency.dispatched')
            : config('fieldservice.workflow_events.order_created', 'fieldservice.order.created');

        $this->dispatchToWorkflow($eventKey, ServiceOrder::class, $order->id, $payload);
    }

    public function triggerOrderDispatched(ServiceOrder $order, Collection $eligibleTechnicians): void
    {
        $payload = [
            'order_id'          => $order->id,
            'order_number'      => $order->order_number,
            'radius_km'         => $order->search_radius_km,
            'technicians_count' => $eligibleTechnicians->count(),
            'technician_ids'    => $eligibleTechnicians->pluck('id')->toArray(),
        ];

        $eventKey = config('fieldservice.workflow_events.order_dispatched', 'fieldservice.order.dispatched');
        $this->dispatchToWorkflow($eventKey, ServiceOrder::class, $order->id, $payload);
    }

    public function triggerOrderAccepted(ServiceOrder $order, Technician $technician): void
    {
        $payload = [
            'order_id'          => $order->id,
            'order_number'      => $order->order_number,
            'technician_id'     => $technician->id,
            'technician_name'   => $technician->full_name,
            'technician_mobile' => $technician->mobile,
            'customer_phone'    => $order->customer_phone,
            'customer_name'     => $order->customer_name,
        ];

        $eventKey = config('fieldservice.workflow_events.order_accepted', 'fieldservice.order.accepted');
        $this->dispatchToWorkflow($eventKey, ServiceOrder::class, $order->id, $payload);
    }

    public function triggerTechnicianArrived(ServiceOrder $order): void
    {
        $payload = [
            'order_id'          => $order->id,
            'order_number'      => $order->order_number,
            'technician_id'     => $order->technician_id,
            'customer_phone'    => $order->customer_phone,
            'arrived_at'        => $order->arrived_at?->toIso8601String(),
        ];

        $eventKey = config('fieldservice.workflow_events.technician_arrived', 'fieldservice.technician.arrived');
        $this->dispatchToWorkflow($eventKey, ServiceOrder::class, $order->id, $payload);
    }

    public function triggerInvoiceSubmitted(ServiceInvoice $invoice): void
    {
        $order = $invoice->order;
        $payload = [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'order_id'       => $order?->id,
            'order_number'   => $order?->order_number,
            'total_amount'   => $invoice->total_amount,
            'labor_amount'   => $invoice->labor_amount,
            'parts_amount'   => $invoice->parts_amount,
            'travel_fee'     => $invoice->travel_fee,
            'customer_phone' => $order?->customer_phone,
        ];

        $eventKey = config('fieldservice.workflow_events.invoice_submitted', 'fieldservice.invoice.submitted');
        $this->dispatchToWorkflow($eventKey, ServiceOrder::class, $order?->id ?? 0, $payload);
    }

    public function triggerInvoicePaid(ServiceInvoice $invoice): void
    {
        $order = $invoice->order;
        $payload = [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'order_id'       => $order?->id,
            'order_number'   => $order?->order_number,
            'total_amount'   => $invoice->total_amount,
            'escrow_held'    => true,
            'warranty_until' => $invoice->warranty_until?->toDateString(),
            'customer_phone' => $order?->customer_phone,
        ];

        $eventKey = config('fieldservice.workflow_events.invoice_paid', 'fieldservice.invoice.paid');
        $this->dispatchToWorkflow($eventKey, ServiceOrder::class, $order?->id ?? 0, $payload);
    }

    public function triggerOrderCompleted(ServiceOrder $order): void
    {
        $payload = [
            'order_id'          => $order->id,
            'order_number'      => $order->order_number,
            'technician_id'     => $order->technician_id,
            'client_id'         => $order->client_id,
            'customer_phone'    => $order->customer_phone,
            'completed_at'      => $order->completed_at?->toIso8601String(),
        ];

        $eventKey = config('fieldservice.workflow_events.order_completed', 'fieldservice.order.completed');
        $this->dispatchToWorkflow($eventKey, ServiceOrder::class, $order->id, $payload);
    }

    public function triggerBackupRequested(BackupRequest $backup): void
    {
        $order = $backup->order;
        $payload = [
            'backup_id'             => $backup->id,
            'order_id'              => $order?->id,
            'order_number'          => $order?->order_number,
            'primary_technician_id' => $backup->primary_technician_id,
            'reason'                => $backup->reason,
            'notes'                 => $backup->notes,
        ];

        $eventKey = config('fieldservice.workflow_events.backup_requested', 'fieldservice.backup.requested');
        $this->dispatchToWorkflow($eventKey, ServiceOrder::class, $order?->id ?? 0, $payload);
    }
}
