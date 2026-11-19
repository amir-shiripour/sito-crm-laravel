<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // اگر کاربر لاگین نکرده باشد، به صفحه ورود هدایت می‌شود
        if (!$user) {
            return redirect()->guest(route('login'));
        }

        // بررسی دسترسی سوپر ادمین
        $isSuperAdmin = !empty($user->is_super_admin) ||
            (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
            (method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('Super Admin') || $user->hasRole('superadmin')));

        // در صورتی که کاربر سوپر ادمین نباشد، طبق خواسته به روت /user/dashboard هدایت می‌شود
        if (!$isSuperAdmin) {
            return redirect('/user/dashboard');
        }

        return $next($request);
    }
}
