<?php

namespace Modules\FieldService\App\Livewire\Customer;

use Livewire\Component;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Services\EscrowFinancialService;
use Modules\FieldService\App\Services\MapProviderService;

class LiveTracking extends Component
{
    public ?int $orderId = null;

    public function mount(?int $orderId = null)
    {
        $this->orderId = $orderId ?: ServiceOrder::latest()->value('id');
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

    public function cancelOrder()
    {
        $order = ServiceOrder::find($this->orderId);
        if ($order && in_array($order->status->value, ['pending', 'searching', 'dispatched'])) {
            $order->status = \Modules\FieldService\App\Enums\OrderStatus::CANCELLED;
            $order->save();

            $this->dispatch('notify', [
                'type' => 'info',
                'text' => 'درخواست اعزام تکنسین لغو شد.',
            ]);
        }
    }

    public function render()
    {
        $order = ServiceOrder::with(['client', 'technician', 'category', 'invoice.items'])
            ->find($this->orderId);

        $mapConfig = app(MapProviderService::class)->getMapConfig();

        return view('fieldservice::livewire.customer.live-tracking', [
            'order'     => $order,
            'mapConfig' => $mapConfig,
        ]);
    }
}
