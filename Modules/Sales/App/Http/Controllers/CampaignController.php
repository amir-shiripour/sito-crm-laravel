<?php

namespace Modules\Sales\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\App\Models\Campaign;

class CampaignController extends Controller
{
    public function index()
    {
        return view('sales::campaigns.index');
    }

    public function create()
    {
        return view('sales::campaigns.create');
    }

    public function store(Request $request)
    {
        // Validation and storing logic will be implemented
    }

    public function show(Campaign $campaign)
    {
        return view('sales::campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign)
    {
        return view('sales::campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        // Update logic
    }

    public function destroy(Campaign $campaign)
    {
        // Delete logic
    }
}
