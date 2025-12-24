<?php

namespace Modules\Clients\App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthenticateClient extends Middleware
{
    /**
     * تعیین گارد پیش‌فرض برای کلاینت‌ها
     */
    protected function authenticate($request, array $guards)
    {
        // اگر گاردی مشخص نشده، client در نظر می‌گیریم
        if (empty($guards)) {
            $guards = ['client'];
        }

        parent::authenticate($request, $guards);
    }

    protected function redirectTo($request): ?string
    {
        // اگر درخواست JSON نبود و لاگین نیست، بفرستش صفحه لاگین پرتال
        if (! $request->expectsJson()) {
            return route('clients.portal.login');
        }

        return null;
    }
}
