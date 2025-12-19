<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module; // مدل دیتابیس شما
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module as NModule;
use App\Services\Modules\BaseModuleInstaller;

class ModuleController extends Controller
{
    protected $installerCache = [];

    /**
     * Display a listing of the modules.
     */
    public function index()
    {
        // 1) دریافت تمام ماژول‌های پکیج (فیزیکی)
        $packageModules = NModule::all();

        // 2) همسان‌سازی (sync) ماژول‌های فیزیکی با جدول modules در دیتابیس
        //    اگر رکوردی برای ماژول فیزیکی وجود ندارد، ایجاد می‌کنیم (is_core=false پیش‌فرض)
        foreach ($packageModules as $pModule) {
            $name = $pModule->getName(); // مثال: "Clients"
            $slug = Str::lower($name);

            // اگر رکوردی با همین slug وجود ندارد، ایجاد کن
            $existing = Module::where('slug', $slug)->first();
            if (! $existing) {
                Module::create([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $pModule->get('description') ?? null,
                    'is_core' => false,
                    'active' => false, // به صورت پیش‌فرض inactive تا کاربر نصب/activate کند
                    'installed' => false,
                ]);
            } else {
                // آپدیت وضعیت فیزیکی (اختیاری، نشان‌دهنده وضعیت واقعی پکیج)
                if ($existing->active != $pModule->isEnabled()) {
                    // فقط همگام‌سازی نمایش؛ فعال/غیرفعال حقیقی بر اساس عمل کاربر خواهد بود
                    $existing->update(['active' => $pModule->isEnabled()]);
                }
            }
        }

        // 3) حالا ماژول‌های دیتابیس (غیر هسته‌ای) را بگیر و وضعیت پکیج را نیز بساز
        $dbModules = Module::where('is_core', false)->get();

        $packageModulesStatus = [];
        foreach ($packageModules as $module) {
            $packageModulesStatus[Str::lower($module->getName())] = $module->isEnabled();
        }

        return view('admin.modules.index', compact('dbModules', 'packageModulesStatus'));
    }

    /**
     * Toggle the status of a module (legacy toggle kept for compatibility).
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'action' => 'required|in:enable,disable',
        ]);

        $slug = $request->slug; // مثال: "clients"
        $action = $request->action;

        $dbModule = Module::where('slug', $slug)->where('is_core', false)->first();

        if (!$dbModule) {
            return back()->with('error', 'ماژول مورد نظر یافت نشد یا هسته‌ای است.');
        }

        $candidateNames = [
            $dbModule->name,
            Str::studly($dbModule->slug),
            Str::ucfirst($dbModule->slug),
        ];

        $packageModule = null;
        foreach ($candidateNames as $cname) {
            $m = NModule::find($cname);
            if ($m) {
                $packageModule = $m;
                break;
            }
        }

        if (! $packageModule) {
            return back()->with('error', "ماژول فیزیکی '{$slug}' یافت نشد.");
        }

        try {
            $command = $action === 'enable' ? 'module:enable' : 'module:disable';
            Artisan::call($command, ['module' => $packageModule->getName()]);
            $output = Artisan::output();

            if (str_contains(strtolower($output), 'error')) {
                Log::error("خطا در $command {$packageModule->getName()}: " . $output);
                return back()->with('error', "خطا در اجرای دستور: " . $output);
            }

            $dbModule->update(['active' => ($action === 'enable')]);

            Artisan::call('optimize:clear');

            return back()->with('success', "ماژول '{$dbModule->name}' با موفقیت " . ($action === 'enable' ? 'فعال' : 'غیرفعال') . " شد.");
        } catch (\Exception $e) {
            Log::error("خطا در toggle ماژول {$slug}: " . $e->getMessage());
            return back()->with('error', 'خطای سیستمی: ' . $e->getMessage());
        }
    }

    /**
     * Install a module (first-time installer). This runs migrations, seeders and permissions.
     */
    public function install(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $slug = $request->slug;

        $dbModule = Module::where('slug', $slug)->where('is_core', false)->firstOrFail();

        if ($dbModule->installed) {
            return back()->with('info', 'ماژول قبلاً نصب شده است.');
        }

        $moduleName = $dbModule->name;
        $installer = $this->resolveInstaller($moduleName);

        try {
            // installer responsable for migrations, seeders, permissions, backups etc.
            $installer->install();

            $dbModule->update([
                'installed' => true,
                'installed_at' => now(),
                'active' => true,
            ]);

            Log::info("Module {$moduleName} installed by user " . auth()->id());

            return back()->with('success', "ماژول '{$dbModule->name}' نصب و فعال شد.");
        } catch (\Throwable $e) {
            Log::error("Install error for {$moduleName}: " . $e->getMessage());
            return back()->with('error', 'خطا در نصب ماژول: ' . $e->getMessage());
        }
    }

    /**
     * Enable an already-installed module (or install if missing).
     */
    public function enableModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $slug = $request->slug;

        $dbModule = Module::where('slug', $slug)->firstOrFail();
        $moduleName = $dbModule->name;

        $installer = $this->resolveInstaller($moduleName);

        try {
            if (! $dbModule->installed) {
                $installer->install();
                $dbModule->update([
                    'installed' => true,
                    'installed_at' => now(),
                ]);
            } else {
                $installer->enable();
            }

            $dbModule->update(['active' => true]);

            Log::info("Module {$moduleName} enabled by user " . auth()->id());

            return back()->with('success', "ماژول '{$dbModule->name}' فعال شد.");
        } catch (\Throwable $e) {
            Log::error("Enable error for {$moduleName}: " . $e->getMessage());
            return back()->with('error', 'خطا در فعال‌سازی ماژول: ' . $e->getMessage());
        }
    }

    /**
     * Disable module (do not delete data).
     */
    public function disableModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $slug = $request->slug;

        $dbModule = Module::where('slug', $slug)->firstOrFail();
        $moduleName = $dbModule->name;

        $installer = $this->resolveInstaller($moduleName);

        try {
            $installer->disable();

            $dbModule->update(['active' => false]);

            Log::info("Module {$moduleName} disabled by user " . auth()->id());

            return back()->with('success', "ماژول '{$dbModule->name}' غیرفعال شد.");
        } catch (\Throwable $e) {
            Log::error("Disable error for {$moduleName}: " . $e->getMessage());
            return back()->with('error', 'خطا در غیرفعال‌سازی ماژول: ' . $e->getMessage());
        }
    }

    /**
     * Reset module data to initial seeded state (destructive: truncates module tables then reseeds).
     * WARNING: destructive operation — Installer should backup data first.
     */
    public function resetModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $slug = $request->slug;

        $dbModule = Module::where('slug', $slug)->firstOrFail();
        $moduleName = $dbModule->name;

        $installer = $this->resolveInstaller($moduleName);

        try {
            // Installer should create backup before truncating
            $installer->reset();

            Log::info("Module {$moduleName} reset by user " . auth()->id());

            return back()->with('success', "ماژول '{$dbModule->name}' به‌حالت اولیه بازگردانده شد.");
        } catch (\Throwable $e) {
            Log::error("Reset error for {$moduleName}: " . $e->getMessage());
            return back()->with('error', 'خطا در ریست ماژول: ' . $e->getMessage());
        }
    }

    /**
     * Uninstall a module: disable, run uninstall routine (drop tables if configured), remove files.
     * WARNING: destructive — backups mandatory.
     */
    public function uninstallModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $slug = $request->slug;

        $dbModule = Module::where('slug', $slug)->firstOrFail();
        $moduleName = $dbModule->name;

        $installer = $this->resolveInstaller($moduleName);

        try {
            // create backup (installer should handle it)
            $installer->disable();

            // call uninstall which should drop tables and remove files as defined by module
            $installer->uninstall();

            // remove DB record
            $dbModule->delete();

            Log::warning("Module {$moduleName} uninstalled by user " . auth()->id());

            return back()->with('success', "ماژول '{$moduleName}' حذف شد (فایل‌ها و DB اگر ماژول انجام داده باشد).");
        } catch (\Throwable $e) {
            Log::error("Uninstall error for {$moduleName}: " . $e->getMessage());
            return back()->with('error', 'خطا در حذف ماژول: ' . $e->getMessage());
        }
    }

    /**
     * Resolve installer class for a module; fallback to BaseModuleInstaller.
     */
    protected function resolveInstaller(string $moduleName)
    {
        if (isset($this->installerCache[$moduleName])) {
            return $this->installerCache[$moduleName];
        }

        $installerClass = "\\Modules\\{$moduleName}\\Installer";

        if (class_exists($installerClass)) {
            $installer = new $installerClass();
        } else {
            // fallback: use a generic installer (will run migrations/seeders if present)
            $installer = new BaseModuleInstaller($moduleName);
        }

        $this->installerCache[$moduleName] = $installer;

        return $installer;
    }
}
