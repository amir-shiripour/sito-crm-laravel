<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\MapProviderService;

class FleetMonitoringController extends Controller
{
    public function index(MapProviderService $mapProvider): View
    {
        $mapConfig = $mapProvider->getMapConfig();

        $technicians = Technician::where('is_online', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get();

        $activeOrders = ServiceOrder::whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['client', 'technician'])
            ->get();

        return view('fieldservice::admin.fleet-monitoring', compact('mapConfig', 'technicians', 'activeOrders'));
    }
}
