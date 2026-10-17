<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TechnicianManagementController extends Controller
{
    public function index(): View
    {
        return view('fieldservice::admin.technicians-index');
    }
}
