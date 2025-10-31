<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * نمایش صفحه اصلی سایت (فرانت).
     */
    public function home()
    {
        // این صفحه اصلی سایت شماست
        return view('welcome');
    }
}

