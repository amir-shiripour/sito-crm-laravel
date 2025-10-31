<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class CheckInstallationStatus
{

    public function handle(Request $request, Closure $next)
    {
        // مسیر فایل پرچم نصب
        $installedFlagPath = storage_path('app/installed.flag');

        // ۱. آیا برنامه نصب شده است؟
        if (File::exists($installedFlagPath)) {
            // بله. پس هیچ کاری نکن. بگذار برنامه به صورت عادی اجرا شود.
            return $next($request);
        }

        // ۲. برنامه نصب نشده است.

        // ۳. آیا درخواست فعلی برای خود صفحه نصب‌کننده یا خطایابی است؟
        if ($this->isInstallRoute($request)) {
            // بله. پس باید سشن را به 'file' تغییر دهیم تا این صفحه کرش نکند.
            // هر دو روش را برای اطمینان کامل انجام می‌دهیم.
            Config::set('session.driver', 'file');
            session()->setDefaultDriver('file');

            // حالا بگذار درخواست به صفحه نصب‌کننده ادامه یابد.
            return $next($request);
        }

        // ۴. برنامه نصب نشده و درخواست برای صفحه دیگری است (مثلاً صفحه اصلی).
        // درخواست را همینجا متوقف کن و کاربر را به نصب‌کننده هدایت کن.
        // این کار از اجرای میدل‌ور StartSession جلوگیری می‌کند.
        return redirect()->to('/install');
    }

    /**
     * بررسی می‌کند که آیا درخواست فعلی مربوط به مسیر نصب است یا خیر.
     */
    private function isInstallRoute(Request $request): bool
    {
        // اگر مسیر فعلی با 'install' یا '_ignition' (برای خطاها) شروع می‌شود
        // به آن اجازه عبور می‌دهیم.
        return $request->is('install') || $request->is('install/*') || $request->is('_ignition/*');
    }
}

