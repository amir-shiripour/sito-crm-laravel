<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;

class AccountingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // The view 'accounting::index' refers to /resources/views/index.blade.php
        return view('accounting::index');
    }
}
