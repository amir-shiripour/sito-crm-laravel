<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Settings\Entities\GapGPTLog;

class GapGPTLogController extends Controller
{
    public function index()
    {
        $logs = GapGPTLog::with('user')
            ->latest()
            ->paginate(20);

        return view('settings::logs.index', compact('logs'));
    }

    public function show(GapGPTLog $log)
    {
        return view('settings::logs.show', compact('log'));
    }
}
