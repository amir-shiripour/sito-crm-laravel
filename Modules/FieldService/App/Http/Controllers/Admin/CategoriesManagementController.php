<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class CategoriesManagementController extends Controller
{
    public function index()
    {
        return view('fieldservice::admin.categories-index');
    }
}
