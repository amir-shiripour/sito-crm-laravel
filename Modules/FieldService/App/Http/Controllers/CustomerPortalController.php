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
        $activeTechniciansCount = Technician::where('is_online', true)->count();
        $latestOrder = ServiceOrder::latest()->first();

        return view('fieldservice::customer.index', compact('mapConfig', 'categories', 'activeTechniciansCount', 'latestOrder'));
    }
}
