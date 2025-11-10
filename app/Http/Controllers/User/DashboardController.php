<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Modules\ModuleMenuService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ModuleMenuService $menuService;

    public function __construct(ModuleMenuService $menuService)
    {
        $this->middleware('auth');
        $this->menuService = $menuService;
    }

    public function index()
    {
        $user = auth()->user();
        $menuItems = $this->menuService->getAllForUser($user);

        return view('user.dashboard', compact('menuItems', 'user'));
    }
}
