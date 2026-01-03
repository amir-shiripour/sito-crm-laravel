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
            if (file_exists($flagPath)) {
                Log::info('[MIDDLEWARE] RedirectIfInstalled: فایل نصب یافت شد، هدایت به صفحه اصلی');
                return redirect('/');
            }

            Log::debug('[MIDDLEWARE] RedirectIfInstalled: ادامه به route');
            return $next($request);
        } catch (\Exception $e) {
            Log::error('[MIDDLEWARE] RedirectIfInstalled خطا', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
