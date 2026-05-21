<?php

namespace Modules\Market\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Market\Entities\MarketSetting;

class CheckMultiVendorMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $storeType = MarketSetting::getValue('system.store_type', 'multi');

        if ($storeType === 'single') {
            // اگر در حالت تک‌فروشندگی بودیم، به صفحه اصلی داشبورد ریدایرکت کن
            // یا هر صفحه دیگری که مناسب می‌دانید
            return redirect()->route('user.dashboard')->with('error', 'این بخش فقط در حالت چندفروشندگی (Marketplace) فعال است.');
        }

        return $next($request);
    }
}
