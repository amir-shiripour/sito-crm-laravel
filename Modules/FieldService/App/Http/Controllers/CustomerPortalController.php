<?php

namespace Modules\FieldService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\FieldServiceSetting;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\MapProviderService;

class CustomerPortalController extends Controller
{
    public function index(MapProviderService $mapProvider): View
    {
        $mapConfig = $mapProvider->getMapConfig();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        
        $onlineTechnicians = Technician::where('is_online', true)
            ->get(['id', 'first_name', 'last_name', 'mobile', 'city', 'lat', 'lng', 'is_busy', 'rating_avg', 'completed_orders_count']);
            
        $activeTechniciansCount = $onlineTechnicians->count();

        // Resolve active/unresolved order for current customer or session
        $clientId = Auth::guard('client')->id();
        $sessionOrderId = session('fieldservice_active_order_id');
        $queryOrderId = request()->query('order_id');

        $activeOrder = ServiceOrder::resolveActiveForClient($clientId, $sessionOrderId, $queryOrderId);

        if ($activeOrder) {
            session(['fieldservice_active_order_id' => $activeOrder->id]);
        } elseif ($sessionOrderId) {
            $sessOrder = ServiceOrder::find($sessionOrderId);
            if (!$sessOrder || !$sessOrder->isActive()) {
                session()->forget('fieldservice_active_order_id');
            }
        }

        // Dynamically fetch active covered cities with coordinates from FieldServiceSettings
        $coveredCities = FieldServiceSetting::getCoveredCitiesWithCoords();
        $mazandaranCities = $coveredCities;

        return view('fieldservice::customer.index', compact(
            'mapConfig',
            'categories',
            'onlineTechnicians',
            'activeTechniciansCount',
            'activeOrder',
            'coveredCities',
            'mazandaranCities'
        ));
    }
}
