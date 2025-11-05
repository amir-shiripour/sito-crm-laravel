<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    /**
     * Display a listing of the modules.
     */
    public function index()
    {
        // ماژول‌های دیتابیس (فقط آن‌هایی که هسته‌ای نیستند)
        // ماژول‌های هسته‌ای (مثل مدیریت کاربران) نباید در این لیست نمایش داده شوند
        // چون نباید غیرفعال شوند.
        $dbModules = Module::where('is_core', false)->get();

        // وضعیت ماژول‌های فیزیکی از پکیج nwidart
        $packageModules = \Nwidart\Modules\Facades\Module::all();
        $packageModulesStatus = [];
        foreach ($packageModules as $module) {
            $packageModulesStatus[$module->getName()] = $module->isEnabled();
        }

        return view('admin.modules.index', compact('dbModules', 'packageModulesStatus'));
    }

    /**
     * Toggle the status of a module.
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'action' => 'required|in:enable,disable',
        ]);

        $slug = $request->slug;
        $action = $request->action;

        // یافتن ماژول در دیتابیس
        $dbModule = Module::where('slug', $slug)->where('is_core', false)->first();

        if (!$dbModule) {
            return back()->with('error', 'ماژول مورد نظر یافت نشد یا هسته‌ای است.');
        }

        // یافتن ماژول فیزیکی
        $module = \Nwidart\Modules\Facades\Module::find($slug);
        if (!$module) {
            return back()->with('error', "ماژول فیزیکی '{$slug}' یافت نشد.");
        }

        try {
            // 1. اجرای دستور Artisan
            $command = $action === 'enable' ? 'module:enable' : 'module:disable';
            Artisan::call($command, ['module' => $slug]);

            // دریافت خروجی (برای بررسی خطا)
            $output = Artisan::output();

            // اصلاح شد: از count() روی رشته استفاده نمی‌کنیم
            if (str_contains(strtolower($output), 'error')) {
                Log::error("خطا در $command $slug: " . $output);
                return back()->with('error', "خطا در اجرای دستور: " . $output);
            }

            // 2. آپدیت دیتابیس ما
            $dbModule->update(['active' => ($action === 'enable')]);

            // 3. پاک کردن کش‌ها
            Artisan::call('optimize:clear');

            return back()->with('success', "ماژول '{$dbModule->name}' با موفقیت " . ($action === 'enable' ? 'فعال' : 'غیرفعال') . " شد.");

        } catch (\Exception $e) {
            Log::error("خطا در toggle ماژول $slug: " . $e->getMessage());
            return back()->with('error', 'خطای سیستمی: ' . $e->getMessage());
        }
    }
}

