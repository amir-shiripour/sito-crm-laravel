<?php

namespace Modules\FieldService\App\Livewire\Technician;

use Livewire\Component;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\BackupRequest;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\DispatchRadarService;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;

class MissionFeed extends Component
{
    public ?int $technicianId = null;
    public bool $showBackupModal = false;
    public ?int $backupOrderId = null;
    public string $backupReason = 'heavy_lifting';
    public string $backupNotes = '';

    public function mount(?int $technicianId = null)
    {
        $tech = $technicianId ? Technician::find($technicianId) : Technician::first();
        $this->technicianId = $tech?->id;
    }

    public function acceptMission(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        $tech = Technician::find($this->technicianId);

        if ($order && $tech) {
            $radar = app(DispatchRadarService::class);
            if ($radar->acceptOrder($order, $tech)) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'text' => "سرویس #{$order->order_number} با موفقیت توسط شما پذیرفته شد.",
                ]);
            }
        }
    }

    public function startRoute(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $order->status = OrderStatus::EN_ROUTE;
            $order->en_route_at = now();
            $order->save();

            $this->dispatch('notify', [
                'type' => 'info',
                'text' => 'وضعیت به «در مسیر به سمت مشتری» تغییر یافت. مسیریابی فعال شد.',
            ]);
        }
    }

    public function markArrived(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $order->status = OrderStatus::ARRIVED;
            $order->arrived_at = now();
            $order->save();

            app(FieldServiceWorkflowBridgeService::class)->triggerTechnicianArrived($order);

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'اعلام حضور در محل مشتری ثبت و پیامک اطلاع‌رسانی ارسال گردید.',
            ]);
        }
    }

    public function openBackupModal(int $orderId)
    {
        $this->backupOrderId = $orderId;
        $this->showBackupModal = true;
    }

    public function submitBackupRequest()
    {
        if (!$this->backupOrderId) return;

        $order = ServiceOrder::find($this->backupOrderId);
        $tech = Technician::find($this->technicianId);

        if ($order && $tech) {
            $backup = BackupRequest::create([
                'order_id'              => $order->id,
                'primary_technician_id' => $tech->id,
                'reason'                => $this->backupReason,
                'notes'                 => $this->backupNotes,
                'status'                => 'pending',
                'requested_at'          => now(),
            ]);

            app(FieldServiceWorkflowBridgeService::class)->triggerBackupRequested($backup);

            $this->showBackupModal = false;
            $this->reset(['backupReason', 'backupNotes']);

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'درخواست اعزام تکنسین کمکی در رادار ارسال شد.',
            ]);
        }
    }

    public function render()
    {
        $tech = Technician::find($this->technicianId);

        $availableMissions = ServiceOrder::whereIn('status', [OrderStatus::SEARCHING, OrderStatus::DISPATCHED])
            ->with(['client', 'category'])
            ->latest()
            ->take(5)
            ->get();

        $activeMission = ServiceOrder::where('technician_id', $this->technicianId)
            ->whereIn('status', [OrderStatus::ACCEPTED, OrderStatus::EN_ROUTE, OrderStatus::ARRIVED, OrderStatus::IN_PROGRESS, OrderStatus::INVOICE_PENDING])
            ->with(['client', 'category', 'invoice.items'])
            ->latest()
            ->first();

        return view('fieldservice::livewire.technician.mission-feed', [
            'tech'              => $tech,
            'availableMissions' => $availableMissions,
            'activeMission'     => $activeMission,
        ]);
    }
}
