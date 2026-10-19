<?php

namespace Modules\FieldService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\FieldService\App\Models\Category;
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

        // Mazandaran Cities with coordinates
        $mazandaranCities = [
            'بابل'       => ['lat' => 36.5444, 'lng' => 52.6780, 'name' => 'بابل (مرکز عملیات)'],
            'ساری'       => ['lat' => 36.5659, 'lng' => 53.0586, 'name' => 'ساری'],
            'آمل'        => ['lat' => 36.4676, 'lng' => 52.3507, 'name' => 'آمل'],
            'قائمشهر'    => ['lat' => 36.4646, 'lng' => 52.8596, 'name' => 'قائمشهر'],
            'بابلسر'     => ['lat' => 36.7024, 'lng' => 52.6575, 'name' => 'بابلسر'],
            'محمودآباد'  => ['lat' => 36.6319, 'lng' => 52.2629, 'name' => 'محمودآباد'],
        ];

        return view('fieldservice::customer.index', compact(
            'mapConfig',
            'categories',
            'onlineTechnicians',
            'activeTechniciansCount',
            'latestOrder',
            'mazandaranCities'
        ));
    }
}
