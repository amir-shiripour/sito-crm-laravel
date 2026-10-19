<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BuildingsManagementController extends Controller
{
    public function index(): View
    {
        return view('fieldservice::admin.buildings-index');
    }
}
