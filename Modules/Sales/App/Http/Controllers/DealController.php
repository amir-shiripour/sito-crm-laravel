<?php

declare(strict_types=1);

namespace Modules\Sales\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class DealController extends Controller
{
    public function show(Request $request, $dealId)
    {
        return view('sales::deal-show', [
            'dealId' => (int) $dealId
        ]);
    }
}
