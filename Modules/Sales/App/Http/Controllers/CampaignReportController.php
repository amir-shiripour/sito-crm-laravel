<?php

namespace Modules\Sales\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\App\Models\Campaign;

class CampaignReportController extends Controller
{
    public function index()
    {
        return view('sales::reports.index');
    }

    public function show(Campaign $campaign)
    {
        return view('sales::reports.show', compact('campaign'));
    }
}
