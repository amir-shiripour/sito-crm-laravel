<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * نمایش داشبورد اصلی ادمین.
     */
    public function index()
    {
        // در اینجا بر اساس نقش کاربر (auth()->user()->getRoleNames())
        // می‌توانید ویجت‌ها یا داده‌های مختلفی را به ویو پاس دهید.
        // $widgets = \App\Services\DashboardWidgetManager::getWidgetsForUser(auth()->user());

        return view('admin.dashboard', [
            'user' => auth()->user()
            // 'widgets' => $widgets
        ]);
    }
}

