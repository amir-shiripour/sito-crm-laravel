<?php

namespace Modules\FieldService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\MapProviderService;

class TechnicianPortalController extends Controller
{
    public function index(MapProviderService $mapProvider): View
    {
        $mapConfig = $mapProvider->getMapConfig();
        
        // Load active technician (default to first active or create sample context)
        $technician = Technician::first() ?: new Technician([
            'first_name' => 'محمد',
            'last_name'  => 'قاسمی',
            'mobile'     => '09111112233',
            'is_online'  => true,
        ]);

        $activeMission = ServiceOrder::where('technician_id', $technician->id)
            ->whereIn('status', [OrderStatus::ACCEPTED, OrderStatus::EN_ROUTE, OrderStatus::ARRIVED, OrderStatus::IN_PROGRESS, OrderStatus::INVOICE_PENDING])
            ->with(['client', 'category', 'invoice.items'])
            ->latest()
            ->first();

        $availableMissions = ServiceOrder::whereIn('status', [OrderStatus::SEARCHING, OrderStatus::DISPATCHED])
            ->with(['client', 'category'])
            ->latest()
            ->take(5)
            ->get();

        return view('fieldservice::technician.index', compact('mapConfig', 'technician', 'activeMission', 'availableMissions'));
    }
}
