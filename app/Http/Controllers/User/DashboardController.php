<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Modules\ModuleMenuService;
use Illuminate\Http\Request;
use App\Models\UserDashboardSetting;

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

        // هدایت خودکار به میزکار میزبانی: صرفاً در صورت فعال بودن در تنظیمات املاک و زمانی که کاربر تنها نقش میزبان دارد (کاربران چندنقشی محدود نمی‌شوند)
        $redirectHostToDashboard = class_exists(\Modules\Properties\Entities\PropertySetting::class)
            ? (bool) \Modules\Properties\Entities\PropertySetting::get('rental_redirect_host_to_dashboard', 1)
            : false;

        $userRoles = $user->getRoleNames();
        $isOnlyHost = $userRoles->contains('property_host') && $userRoles->count() === 1;

        if ($redirectHostToDashboard && $isOnlyHost) {
            if (\Illuminate\Support\Facades\Route::has('user.properties.hosts.dashboard')) {
                return redirect()->route('user.properties.hosts.dashboard');
            }
        }

        $menuItems = $this->menuService->getAllForUser($user);

        return view('user.dashboard', compact('menuItems', 'user'));
    }

    public function updateLayout(Request $request)
    {
        $request->validate([
            'layout' => 'required|array',
        ]);

        $user = auth()->user();

        UserDashboardSetting::updateOrCreate(
            ['user_id' => $user->id],
            ['layout' => $request->layout]
        );

        return response()->json(['success' => true]);
    }
}
