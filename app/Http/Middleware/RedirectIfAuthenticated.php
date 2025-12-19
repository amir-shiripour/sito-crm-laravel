<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                // 🔹 اگر کلاینت لاگین است → بفرستش به داشبورد خودش
                if ($guard === 'client') {
                    return redirect()->route('client.dashboard');
                }

                $user = Auth::guard($guard)->user();

                // 🔹 اگر سوپر ادمین است → بفرستش به داشبورد ادمین
                if ($guard === 'web' && $user && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                    return redirect('/admin/dashboard');
                }

                // سایر کاربران (و پیش‌فرض‌ها)
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
