<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('fieldservice::admin.settings-index');
    }
}
