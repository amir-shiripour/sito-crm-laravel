<?php

namespace Modules\FieldService\App\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $latestOrder = ServiceOrder::latest()->first();

        // Dynamically fetch active covered cities with coordinates from FieldServiceSettings
        $coveredCities = FieldServiceSetting::getCoveredCitiesWithCoords();
        $mazandaranCities = $coveredCities;

        return view('fieldservice::customer.index', compact(
            'mapConfig',
            'categories',
            'onlineTechnicians',
            'activeTechniciansCount',
            'latestOrder',
            'coveredCities',
            'mazandaranCities'
        ));
    }
}
