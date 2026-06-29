<?php

namespace Modules\Sales\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesSettingsController extends Controller
{
    public function index()
    {
        return view('sales::settings.index');
    }
}
