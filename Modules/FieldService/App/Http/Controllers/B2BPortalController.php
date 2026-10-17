<?php

namespace Modules\FieldService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\VendorPartner;

class B2BPortalController extends Controller
{
    public function index(): View
    {
        $buildings = Building::with(['client', 'equipments', 'slaContracts', 'orders'])
            ->latest()
            ->get();

        $vendors = VendorPartner::with('orders')->latest()->get();

        return view('fieldservice::b2b.index', compact('buildings', 'vendors'));
    }
}
