<?php

namespace Modules\Properties\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * نمایش صفحه ایمپورت املاک
     */
    public function index()
    {
        return view('properties::user.settings.csv-importer');
    }
}
