<?php

namespace Modules\FieldService\App\Livewire\Customer;

use Livewire\Component;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Services\EscrowFinancialService;
use Modules\FieldService\App\Services\MapProviderService;

class LiveTracking extends Component
{
    public ?int $orderId = null;

    // Cancellation Modal State
    public bool $showCancelModal = false;
    public string $cancelReason = 'schedule_changed';
    public string $cancelNote = '';

    public function getListeners()
    {
        $listeners = [
            'refreshTracking' => '$refresh',
        ];

        if ($this->orderId) {
            $listeners["echo:orders.{$this->orderId},ServiceOrderStatusChanged"] = 'onOrderStatusChanged';
            $listeners["echo:orders.{$this->orderId},.ServiceOrderStatusChanged"] = 'onOrderStatusChanged';
            $listeners["echo:orders.{$this->orderId},TechnicianLocationUpdated"] = 'onTechnicianLocationUpdated';
            $listeners["echo:orders.{$this->orderId},.TechnicianLocationUpdated"] = 'onTechnicianLocationUpdated';
        }

        return $listeners;
    }

    public function mount(?int $orderId = null)
    {
        if ($orderId) {
            $this->orderId = $orderId;
        } else {
            $clientId = \Illuminate\Support\Facades\Auth::guard('client')->id();
            $sessionOrderId = session('fieldservice_active_order_id');
            $active = ServiceOrder::resolveActiveForClient($clientId, $sessionOrderId);
            $this->orderId = $active ? $active->id : null;
        }
    }

    public function onOrderStatusChanged(array $event)
    {
        $this->dispatch('order-status-live-updated', $event);

        if (!empty($event['technician']) && !empty($event['technician']['lat']) && !empty($event['technician']['lng'])) {
            $this->dispatch('technician-assigned', [
                'lat'    => (float) $event['technician']['lat'],
                'lng'    => (float) $event['technician']['lng'],
                'name'   => $event['technician']['full_name'] ?? 'تکنسین',
                'mobile' => $event['technician']['mobile'] ?? '',
            ]);
        }

        $label = $event['status_label'] ?? 'وضعیت به‌روز شد';
        $this->dispatch('notify', [
            'type' => 'info',
            'text' => "وضعیت سفارش شما: {$label}",
        ]);
    }

    public function onTechnicianLocationUpdated(array $event)
    {
        $this->dispatch('technician-location-live-updated', $event);
    }

    public function payAndApproveInvoice()
    {
        $order = ServiceOrder::with('invoice')->find($this->orderId);
        if ($order && $order->invoice) {
            $escrow = app(EscrowFinancialService::class);
            $escrow->lockInEscrow($order->invoice, 'online', 'ONLINE-' . time());

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'مبلغ فاکتور در حساب امن امانی واریز شد و گارانتی خدمت فعال گردید.',
            ]);
        }
    }

    public function confirmSatisfactionAndComplete()
    {
        $order = ServiceOrder::with('invoice')->find($this->orderId);
        if ($order && $order->invoice) {
            $escrow = app(EscrowFinancialService::class);
            $escrow->releaseEscrowToTechnician($order->invoice);

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'با سپاس از اعتماد شما، وجه به تکنسین تسویه و کارت گارانتی ۹۰ روزه صادر شد.',
            ]);
        }
    }

    public function openCancelModal(): void
    {
        $this->cancelReason = 'schedule_changed';
        $this->cancelNote = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
    }

    public function confirmCustomerCancel(): void
    {
        $order = ServiceOrder::find($this->orderId);
        if ($order && $order->canCustomerCancel()) {
            $reasons = ServiceOrder::customerCancellationReasons();
            $reasonTitle = $reasons[$this->cancelReason] ?? 'لغو توسط مشتری';
            $fullReason = $reasonTitle . (!empty($this->cancelNote) ? ' - ' . trim($this->cancelNote) : '');

            $order->status = \Modules\FieldService\App\Enums\OrderStatus::CANCELLED;
            $order->cancelled_at = now();
            $order->cancellation_reason = $fullReason;
            $meta = $order->meta ?? [];
            $meta['cancelled_by'] = 'customer';
            $meta['cancel_reason_key'] = $this->cancelReason;
            $order->meta = $meta;
            $order->save();

            $this->showCancelModal = false;

            $this->dispatch('notify', [
                'type' => 'info',
                'text' => 'درخواست اعزام تکنسین با موفقیت لغو شد.',
            ]);

            $this->dispatch('order-cancelled-by-customer');
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'text' => 'امکان لغو خودکار در این مرحله وجود ندارد. لطفاً با پشتیبانی تماس بگیرید.',
            ]);
        }
    }

    public function render()
    {
        $order = ServiceOrder::with(['client', 'technician', 'category', 'invoice.items'])
            ->find($this->orderId);

        $mapConfig = app(MapProviderService::class)->getMapConfig();

        return view('fieldservice::livewire.customer.live-tracking', [
            'order'               => $order,
            'mapConfig'           => $mapConfig,
            'cancellationReasons' => ServiceOrder::customerCancellationReasons(),
        ]);
    }
}
