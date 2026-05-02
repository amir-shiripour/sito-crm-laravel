<?php

namespace Modules\Market\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckVendorStatus
{
    /**
     * بررسی وضعیت پروفایل فروشنده قبل از ورود به مسیرهای اختصاصی
     */
    public function handle(Request $request, Closure $next)
    {
        $vendor = auth()->user()?->marketVendor;

        // ۱. اصلاً فروشگاه دارد؟
        if (!$vendor) {
            abort(403, 'شما هنوز پروفایل فروشگاهی ندارید.');
        }

        // ۲. آیا فروشگاهش در انتظار تایید است؟
        if ($vendor->status === 'pending') {
            abort(403, 'فروشگاه شما در انتظار تایید مدیریت است. تا زمان تایید نهایی، امکان مدیریت محصولات را ندارید.');
        }

        // ۳. آیا فروشگاهش مسدود شده؟
        if ($vendor->status === 'suspended') {
            abort(403, 'فروشگاه شما موقتاً مسدود شده است. لطفاً با پشتیبانی تماس بگیرید.');
        }

        // اگر وضعیتش active بود، اجازه عبور بده
        return $next($request);
    }
}
