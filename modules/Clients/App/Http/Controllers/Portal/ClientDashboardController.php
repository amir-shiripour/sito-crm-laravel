<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();

        return view('clients::portal.dashboard', compact('client'));
    }
}
