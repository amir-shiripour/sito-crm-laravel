<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Livewire\Component;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\MapProviderService;

class FleetLiveMap extends Component
{
    public string $selectedCity = 'all';
    public string $selectedFilter = 'all'; // all, online, busy, orders

    public function render()
    {
        $mapProvider = app(MapProviderService::class);
        $mapConfig = $mapProvider->getMapConfig();

        $techQuery = Technician::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng');

        if ($this->selectedCity !== 'all') {
            $techQuery->where('city', $this->selectedCity);
        }

        if ($this->selectedFilter === 'online') {
            $techQuery->where('is_online', true)->where('is_busy', false);
        } elseif ($this->selectedFilter === 'busy') {
            $techQuery->where('is_busy', true);
        }

        $technicians = $techQuery->get();

        $orderQuery = ServiceOrder::query()
            ->whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['client', 'technician']);

        if ($this->selectedCity !== 'all') {
            $orderQuery->where('city', $this->selectedCity);
        }

        $activeOrders = $orderQuery->get();

        $stats = [
            'online_count' => Technician::where('is_online', true)->count(),
            'busy_count'   => Technician::where('is_busy', true)->count(),
            'orders_count' => ServiceOrder::whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])->count(),
            'total_techs'  => Technician::count(),
        ];

        return view('fieldservice::livewire.admin.fleet-live-map', [
            'mapConfig'    => $mapConfig,
            'technicians'  => $technicians,
            'activeOrders' => $activeOrders,
            'stats'        => $stats,
        ]);
    }
}
