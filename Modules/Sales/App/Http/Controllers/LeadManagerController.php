<?php

declare(strict_types=1);

namespace Modules\Sales\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class LeadManagerController extends Controller
{
    public function index(Request $request)
    {
        return view('sales::lead-manager');
    }
}
