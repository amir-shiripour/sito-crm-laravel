<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ModuleController extends Controller
{
    /**
     * نمایش لیست تمام ماژول‌ها.
     */
    public function index()
    {
        // تمام ماژول‌ها را از دیتابیس می‌گیریم و بر اساس نام مرتب می‌کنیم
        $modules = Module::orderBy('name')->get();

        // ویو را به همراه لیست ماژول‌ها برمی‌گردانیم
        return view('admin.modules.index', compact('modules'));
    }

    /**
     * وضعیت یک ماژول (فعال/غیرفعال) را تغییر می‌دهد.
     */
    public function toggle(Request $request, Module $module)
    {
        // اطمینان حاصل می‌کنیم که ماژول 'core' هرگز غیرفعال نمی‌شود
        if ($module->slug === 'core') {
            return redirect()->route('admin.modules.index')
                ->with('error', 'ماژول هسته (Core) قابل غیرفعال‌سازی نیست.');
        }

        // وضعیت ماژول را برعکس می‌کنیم (اگر true بود false می‌شود و برعکس)
        $module->active = !$module->active;
        $module->save();

        // پاک کردن کش‌های مربوط به ماژول‌ها و تم‌ها
        // این کار مهم است تا تغییرات در سراسر برنامه اعمال شوند
        Cache::forget('active_theme'); // کش تم فعال (چون ممکن است نیازمندی‌هایش تغییر کند)
        Cache::forget('active_modules'); // کش ماژول‌های فعال (اگر چنین کشی دارید)

        // (اختیاری) اجرای دستورات مربوط به ماژول
        // اگر ماژول‌ها ServiceProvider یا Migrations دارند،
        // در اینجا باید منطق مربوط به فعال‌سازی آن‌ها را اجرا کنید
        // (مثلاً: Artisan::call('module:migrate', ['module' => $module->slug]))

        // کاربر را به صفحه مدیریت ماژول‌ها برمی‌گردانیم
        return redirect()->route('admin.modules.index')
            ->with('success', "وضعیت ماژول '{$module->name}' با موفقیت تغییر کرد.");
    }
}

