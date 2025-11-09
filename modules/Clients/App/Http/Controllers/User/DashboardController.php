<?php

namespace Modules\Clients\App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // می‌توانید اطلاعات قابل مشاهده برای کاربر جاری را جمع‌آوری کنید
        $user = auth()->user();

        // مثال: خلاصه‌ای از clients مرتبط با کاربر (اگر رابطه وجود دارد)
        // $clients = $user->clients()->paginate(10);

        return view('user.dashboard', compact('user'));
    }
}
