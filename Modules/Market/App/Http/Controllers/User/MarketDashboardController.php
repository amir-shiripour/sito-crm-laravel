<?php

namespace Modules\Market\App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class MarketDashboardController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->marketVendor;

        // ۱. اگر اصلاً فروشگاه نداره -> برو به فرم ویزارد
        if (!$vendor) {
            return view('market::user.kyc.wizard');
        }

        // ۲. اگر ثبت نام کرده ولی در انتظار تایید ادمین هست -> صفحه وضعیت
        if ($vendor->kyc_status === 'pending') {
            return view('market::user.kyc.pending', compact('vendor'));
        }

        // ۳. اگر مدارکش رد شده -> برگرد به ویزارد تا اصلاح کنه
        if ($vendor->kyc_status === 'rejected') {
            return view('market::user.kyc.wizard', compact('vendor'));
        }

        // ۴. اگر تایید شده و فعاله -> داشبورد اصلی مارکت
        if ($vendor->status === 'active') {
            // این فایل رو بعدا میسازیم
            return view('market::user.dashboard.index', compact('vendor'));
        }

        // ۵. اگر مسدود شده
        return view('market::user.dashboard.suspended', compact('vendor'));
    }
}
