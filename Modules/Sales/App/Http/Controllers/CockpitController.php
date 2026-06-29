<?php

namespace Modules\Sales\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CockpitController extends Controller
{
    public function index(Request $request)
    {
        return view('sales::cockpit');
    }
}
