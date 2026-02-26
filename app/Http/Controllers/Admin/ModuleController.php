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

    public function __construct(ModuleUpdaterService $updater)
    {
        $this->updater = $updater;
    }

    /**
     * نمایش لیست ماژول‌ها با همگام‌سازی ورژن فیزیکی و دیتابیس
     */
    public function index()
    {
        $packageModules = NModule::all();

        foreach ($packageModules as $pModule) {
            $name = $pModule->getName();
            $slug = Str::lower($name);
            // دریافت ورژن دستی از فایل module.json
            $versionFromFile = $pModule->get('version') ?? '1.0.0';

            $existing = ModuleModel::where('slug', $slug)->first();
            if (! $existing) {
                ModuleModel::create([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $pModule->get('description') ?? null,
                    'version' => $versionFromFile,
                    'is_core' => false,
                    'active' => false,
                    'installed' => false,
                ]);
            } else {
                // آپدیت خودکار دیتابیس اگر ورژن فیزیکی تغییر کرده باشد
                if ($existing->version !== $versionFromFile || $existing->active != $pModule->isEnabled()) {
                    $existing->update([
                        'version' => $versionFromFile,
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
     * متد جدید: آپدیت پکیج ماژول از طریق ZIP
     */
    public function updatePackage(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'module_zip' => 'required|file|mimes:zip|max:51200', // حداکثر 50 مگابایت
        ]);

        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();

        // ذخیره موقت فایل جهت پردازش
        $path = $request->file('module_zip')->storeAs('temp_updates', 'mod_' . Str::random(8) . '.zip');

        $result = $this->updater->updateFromZip($dbModule->name, storage_path('app/' . $path));

        // پاکسازی فایل زیپ آپلود شده بعد از اتمام
        File::delete(storage_path('app/' . $path));

        if ($result['success']) {
            $dbModule->update(['version' => $result['version']]);
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
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

        $dbModule = ModuleModel::where('slug', $slug)->where('is_core', false)->first();

        if (!$dbModule) {
            return back()->with('error', 'ماژول مورد نظر یافت نشد.');
        }

        try {
            $command = $action === 'enable' ? 'module:enable' : 'module:disable';
            Artisan::call($command, ['module' => $dbModule->name]);

            $dbModule->update(['active' => ($action === 'enable')]);
            Artisan::call('optimize:clear');

            return back()->with('success', "ماژول '{$dbModule->name}' تغییر وضعیت داد.");
        } catch (\Exception $e) {
            return back()->with('error', 'خطای سیستمی: ' . $e->getMessage());
        }
    }

    /**
     * Install a module.
     */
    public function install(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->where('is_core', false)->firstOrFail();

        if ($dbModule->installed) return back()->with('info', 'قبلاً نصب شده است.');

        try {
            $this->resolveInstaller($dbModule->name)->install();
            $dbModule->update(['installed' => true, 'installed_at' => now(), 'active' => true]);
            return back()->with('success', "ماژول '{$dbModule->name}' با موفقیت نصب شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در نصب: ' . $e->getMessage());
        }
    }

    /**
     * Enable module.
     */
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
            return back()->with('success', "ماژول فعال شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا: ' . $e->getMessage());
        }
    }

    /**
     * Disable module.
     */
    public function disableModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();

        try {
            $this->resolveInstaller($dbModule->name)->disable();
            $dbModule->update(['active' => false]);
            return back()->with('success', "ماژول غیرفعال شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا: ' . $e->getMessage());
        }
    }

    /**
     * Reset module.
     */
    public function resetModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();
        try {
            $this->resolveInstaller($dbModule->name)->reset();
            return back()->with('success', "ماژول ریست شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در ریست: ' . $e->getMessage());
        }
    }

    /**
     * Uninstall module.
     */
    public function uninstallModule(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $dbModule = ModuleModel::where('slug', $request->slug)->firstOrFail();
        try {
            $installer = $this->resolveInstaller($dbModule->name);
            $installer->disable();
            $installer->uninstall();
            $dbModule->delete();
            return back()->with('success', "ماژول و اطلاعات آن حذف شد.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در حذف: ' . $e->getMessage());
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
