<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RedirectIfInstalled
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $flagPath = storage_path('app/installed.flag');
            Log::debug('[MIDDLEWARE] RedirectIfInstalled: بررسی فایل نصب', [
                'path' => $request->path(),
                'flag_exists' => file_exists($flagPath)
            ]);

            if (file_exists($flagPath)) {
                Log::info('[MIDDLEWARE] RedirectIfInstalled: فایل نصب یافت شد، هدایت به صفحه اصلی');
                return redirect('/');
            }

            Log::debug('[MIDDLEWARE] RedirectIfInstalled: ادامه به route', [
                'path' => $request->path(),
                'route_name' => $request->route()?->getName()
            ]);
            return $next($request);
        } catch (\Exception $e) {
            Log::error('[MIDDLEWARE] RedirectIfInstalled خطا', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
