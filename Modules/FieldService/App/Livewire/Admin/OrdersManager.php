<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\DispatchRadarService;

class OrdersManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $priorityFilter = 'all';

    public ?int $selectedOrderId = null;
    public ?int $manualTechId = null;

    protected $queryString = ['search', 'statusFilter', 'priorityFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function reDispatch(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $radar = app(DispatchRadarService::class);
            $res = $radar->dispatchOrder($order);

            $count = $res['technicians_count'] ?? 0;
            $radius = $res['radius_km'] ?? $order->search_radius_km;

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "سفارش #{$order->order_number} مجدداً در شعاع {$radius} کیلومتری برای {$count} تکنسین ارسال شد.",
            ]);
        }
    }

    public function assignTechnician(int $orderId, int $technicianId)
    {
        $order = ServiceOrder::find($orderId);
        $tech = Technician::find($technicianId);

        if ($order && $tech) {
            $radar = app(DispatchRadarService::class);
            $radar->acceptOrder($order, $tech);

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "سفارش #{$order->order_number} به تکنسین {$tech->full_name} تخصیص یافت.",
            ]);
        }
    }

    public function cancelOrder(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $order->status = OrderStatus::CANCELLED;
            $order->cancelled_at = now();
            $order->cancellation_reason = 'لغو دستی توسط اپراتور سیستم';
            $order->save();

            if ($order->technician) {
                $order->technician->is_busy = false;
                $order->technician->save();
            }

            $this->dispatch('notify', [
                'type' => 'warning',
                'text' => "سفارش #{$order->order_number} لغو گردید.",
            ]);
        }
    }

    public function render()
    {
        $query = ServiceOrder::query()
            ->with(['client', 'technician', 'category', 'invoice'])
            ->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', "%{$this->search}%")
                  ->orWhere('customer_name', 'like', "%{$this->search}%")
                  ->orWhere('customer_phone', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        $orders = $query->paginate(15);
        $onlineTechs = Technician::where('is_online', true)->get();

        return view('fieldservice::livewire.admin.orders-manager', [
            'orders'      => $orders,
            'onlineTechs' => $onlineTechs,
        ]);
    }
}
