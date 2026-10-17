<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class OrdersManagementController extends Controller
{
    public function index(): View
    {
        return view('fieldservice::admin.orders-index');
    }
}
