<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module as ModuleModel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module as NModule;
use App\Services\Modules\BaseModuleInstaller;
use App\Services\Modules\ModuleUpdaterService;

class ModuleController extends Controller
{
    protected $installerCache = [];
    protected $updater;

    /**
     * تزریق سرویس آپدیت در کانستراکتور
     */
    public function __construct(ModuleUpdaterService $updater)
    {
        $this->updater = $updater;
    }

    /**
     * نمایش لیست با قابلیت همگام‌سازی ورژن
     */
    public function index()
    {
        $packageModules = NModule::all();

        foreach ($packageModules as $pModule) {
            $name = $pModule->getName();
            $slug = Str::lower($name);
            // خواندن ورژن فیزیکی از فایل module.json
            $versionInFile = $pModule->get('version') ?? '1.0.0';

            $existing = ModuleModel::where('slug', $slug)->first();
            if (! $existing) {
                ModuleModel::create([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $pModule->get('description') ?? null,
                    'version' => $versionInFile,
                    'is_core' => false,
                    'active' => false,
                    'installed' => false,
                ]);
            } else {
                // اگر ورژن در فایل تغییر کرده بود، دیتابیس را آپدیت کن
                if ($existing->version !== $versionInFile || $existing->active != $pModule->isEnabled()) {
                    $existing->update([
                        'version' => $versionInFile,
                        'active' => $pModule->isEnabled()
                    ]);
                }
            }
        }

        $dbModules = ModuleModel::where('is_core', false)->get();

        $packageModulesStatus = [];
        foreach ($packageModules as $module) {
            $packageModulesStatus[Str::lower($module->getName())] = $module->isEnabled();
        }

        return view('admin.modules.index', compact('dbModules', 'packageModulesStatus'));
    }

    /**
     * متد جدید: آپدیت پکیج از طریق ZIP
     */
    public function updatePackage(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'module_zip' => 'required|file|mimes:zip|max:51200', // حداکثر 50 مگابایت
        ]);

        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();
        $file = $request->file('module_zip');

        // ذخیره موقت فایل
        $tempPath = $file->storeAs('temp_updates', 'mod_upd_' . Str::random(5) . '.zip');

        $result = $this->updater->updateFromZip($dbModule->name, storage_path('app/' . $tempPath));

        // حذف فایل زیپ آپلود شده
        File::delete(storage_path('app/' . $tempPath));

        if ($result['success']) {
            $dbModule->update(['version' => $result['version']]);
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
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

        $slug = $request->slug;
        $action = $request->action;

        $dbModule = ModuleModel::where('slug', $slug)->where('is_core', false)->first();

        if (!$dbModule) {
            return back()->with('error', 'ماژول مورد نظر یافت نشد یا هسته‌ای است.');
        }

        try {
            $command = $action === 'enable' ? 'module:enable' : 'module:disable';
            Artisan::call($command, ['module' => $dbModule->name]);

            $dbModule->update(['active' => ($action === 'enable')]);

            Artisan::call('optimize:clear');

            return back()->with('success', "ماژول '{$dbModule->name}' با موفقیت " . ($action === 'enable' ? 'فعال' : 'غیرفعال') . " شد.");
        } catch (\Exception $e) {
            return back()->with('error', 'خطای سیستمی: ' . $e->getMessage());
        }
    }

    /**
     * بقیه متدها (install, enableModule, disableModule, resetModule, uninstallModule, resolveInstaller)
     * دقیقاً با همان جزئیات فایل اصلی شما حفظ شده‌اند.
     */
    public function install(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->where('is_core', false)->firstOrFail();
        if ($dbModule->installed) return back()->with('info', 'ماژول قبلاً نصب شده است.');
        try {
            $this->resolveInstaller($dbModule->name)->install();
            $dbModule->update(['installed' => true, 'installed_at' => now(), 'active' => true]);
            return back()->with('success', "ماژول '{$dbModule->name}' نصب و فعال شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در نصب ماژول: ' . $e->getMessage());
        }
    }

    public function enableModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();
        try {
            if (!$dbModule->installed) {
                $this->resolveInstaller($dbModule->name)->install();
                $dbModule->update(['installed' => true, 'installed_at' => now()]);
            } else {
                $this->resolveInstaller($dbModule->name)->enable();
            }
            $dbModule->update(['active' => true]);
            return back()->with('success', "ماژول '{$dbModule->name}' فعال شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در فعال‌سازی: ' . $e->getMessage());
        }
    }

    public function disableModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();
        try {
            $this->resolveInstaller($dbModule->name)->disable();
            $dbModule->update(['active' => false]);
            return back()->with('success', "ماژول '{$dbModule->name}' غیرفعال شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در غیرفعال‌سازی: ' . $e->getMessage());
        }
    }

    public function resetModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();
        try {
            $this->resolveInstaller($dbModule->name)->reset();
            return back()->with('success', "ماژول '{$dbModule->name}' به‌حالت اولیه بازگردانده شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در ریست: ' . $e->getMessage());
        }
    }

    public function uninstallModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();
        try {
            $installer = $this->resolveInstaller($dbModule->name);
            $installer->disable();
            $installer->uninstall();
            $dbModule->delete();
            return back()->with('success', "ماژول با موفقیت حذف شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در حذف ماژول: ' . $e->getMessage());
        }
    }

    protected function resolveInstaller(string $moduleName)
    {
        if (isset($this->installerCache[$moduleName])) return $this->installerCache[$moduleName];
        $installerClass = "\\Modules\\{$moduleName}\\Installer";
        $installer = class_exists($installerClass) ? new $installerClass() : new BaseModuleInstaller($moduleName);
        $this->installerCache[$moduleName] = $installer;
        return $installer;
    }
}
