<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\User;
use App\Services\EnvWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Config;

class InstallController extends Controller
{
    /**
     * نمایش فرم مرحله 1 (تنظیمات دیتابیس)
     */
    public function step1()
    {
        return view('install.step1');
    }

    /**
     * پردازش فرم مرحله 1
     */
    public function processStep1(Request $request)
    {
        Log::info('[INSTALL] مرحله 1 شروع شد', ['ip' => $request->ip()]);

        try {
            $validated = Validator::make($request->all(), [
                'db_host' => 'required|string',
                'db_port' => 'required|numeric',
                'db_database' => 'required|string',
                'db_username' => 'required|string',
                'db_password' => 'nullable|string',
            ])->validate();

            Log::info('[INSTALL] اعتبارسنجی داده‌های فرم با موفقیت انجام شد');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('[INSTALL] خطا در اعتبارسنجی فرم', [
                'errors' => $e->errors(),
                'input' => $request->except(['db_password'])
            ]);
            throw $e;
        }

        // 1. تست اتصال به دیتابیس
        Log::info('[INSTALL] در حال تست اتصال به دیتابیس...', [
            'host' => $validated['db_host'],
            'port' => $validated['db_port'],
            'database' => $validated['db_database'],
            'username' => $validated['db_username']
        ]);

        $dsn = "mysql:host={$validated['db_host']};port={$validated['db_port']};dbname={$validated['db_database']}";
        try {
            $pdo = new \PDO($dsn, $validated['db_username'], $validated['db_password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
            Log::info('[INSTALL] اتصال به دیتابیس با موفقیت برقرار شد');
        } catch (\PDOException $e) {
            Log::error('[INSTALL] خطا در اتصال به دیتابیس', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'dsn' => str_replace($validated['db_password'], '***', $dsn)
            ]);
            return back()->withInput()->withErrors(['db_database' => 'اطلاعات دیتابیس صحیح نیست. خطای اتصال: ' . $e->getMessage()]);
        }

        // 2. نوشتن اطلاعات در فایل .env
        Log::info('[INSTALL] در حال نوشتن تنظیمات دیتابیس در فایل .env...');
        try {
            $envWriter = new EnvWriter();
            $envWriter->overwrite('DB_HOST', $validated['db_host']);
            $envWriter->overwrite('DB_PORT', $validated['db_port']);
            $envWriter->overwrite('DB_DATABASE', $validated['db_database']);
            $envWriter->overwrite('DB_USERNAME', $validated['db_username']);
            $password = $validated['db_password'] ? '"' . $validated['db_password'] . '"' : '';
            $envWriter->overwrite('DB_PASSWORD', $password);
            Log::info('[INSTALL] اطلاعات دیتابیس با موفقیت در فایل .env نوشته شد');
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در نوشتن فایل .env', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['db_database' => 'خطا در نوشتن فایل .env: ' . $e->getMessage()]);
        }

        // 3. پاک کردن کش کانفیگ و اجرای مایگریشن‌ها و سیدرها
        Log::info('[INSTALL] در حال به‌روزرسانی تنظیمات کانفیگ دیتابیس...');
        try {
            Config::set('database.connections.mysql.host', $validated['db_host']);
            Config::set('database.connections.mysql.port', $validated['db_port']);
            Config::set('database.connections.mysql.database', $validated['db_database']);
            Config::set('database.connections.mysql.username', $validated['db_username']);
            Config::set('database.connections.mysql.password', $validated['db_password']);
            DB::purge('mysql');
            Log::info('[INSTALL] تنظیمات کانفیگ دیتابیس به‌روزرسانی شد');
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در به‌روزرسانی کانفیگ دیتابیس', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['db_database' => 'خطا در به‌روزرسانی کانفیگ: ' . $e->getMessage()]);
        }

        // اجرای مایگریشن‌ها
        Log::info('[INSTALL] در حال اجرای مایگریشن‌ها (migrate:fresh)...');
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            $migrationOutput = Artisan::output();
            Log::info('[INSTALL] مایگریشن‌ها با موفقیت اجرا شدند', [
                'output' => $migrationOutput
            ]);
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در اجرای مایگریشن‌ها', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['db_database' => 'خطا در اجرای مایگریشن‌ها: ' . $e->getMessage()]);
        }

        // اجرای سیدرها
        Log::info('[INSTALL] در حال اجرای سیدرها (DatabaseSeeder)...');
        try {
            Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
            $seederOutput = Artisan::output();
            Log::info('[INSTALL] سیدرها با موفقیت اجرا شدند', [
                'output' => $seederOutput
            ]);
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در اجرای سیدرها', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['db_database' => 'خطا در اجرای سیدرها: ' . $e->getMessage()]);
        }

        Log::info('[INSTALL] مرحله 1 با موفقیت تکمیل شد. هدایت به مرحله 2...');

        try {
            // 4. هدایت به مرحله 2
            $redirectUrl = route('install.step2');
            Log::info('[INSTALL] URL هدایت به مرحله 2', ['url' => $redirectUrl]);
            return redirect()->route('install.step2');
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در ایجاد redirect به مرحله 2', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // fallback به redirect ساده
            return redirect('/install/step2');
        }
    }

    /**
     * نمایش فرم مرحله 2 (ایجاد ادمین)
     */
    public function step2()
    {
        // لاگ اولیه - باید اولین خط باشد
        \Illuminate\Support\Facades\Log::info('[INSTALL] متد step2 فراخوانی شد');

        try {
            Log::info('[INSTALL] نمایش فرم مرحله 2 - در حال بارگذاری view...');

            // بررسی وجود view
            $viewPath = resource_path('views/install/step2.blade.php');
            $viewPathAlt = base_path('Resources/views/install/step2.blade.php');

            Log::info('[INSTALL] بررسی مسیر view', [
                'standard_path' => $viewPath,
                'exists_standard' => file_exists($viewPath),
                'alternative_path' => $viewPathAlt,
                'exists_alternative' => file_exists($viewPathAlt)
            ]);

            Log::info('[INSTALL] در حال render کردن view install.step2...');

            // تست ساده برای بررسی مشکل
            try {
                $view = view('install.step2');
                Log::info('[INSTALL] View object ایجاد شد');
                return $view;
            } catch (\Exception $viewException) {
                Log::error('[INSTALL] خطا در ایجاد view object', [
                    'message' => $viewException->getMessage(),
                    'file' => $viewException->getFile(),
                    'line' => $viewException->getLine()
                ]);

                // Fallback به یک view ساده
                return response('
                    <html>
                    <head><title>مرحله 2</title></head>
                    <body>
                        <h1>مرحله 2: ایجاد کاربر ادمین</h1>
                        <p>خطا در بارگذاری view. لطفاً لاگ‌ها را بررسی کنید.</p>
                    </body>
                    </html>
                ', 500);
            }
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در نمایش فرم مرحله 2', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[INSTALL] خطای Throwable در نمایش فرم مرحله 2', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * پردازش فرم مرحله 2
     */
    public function processStep2(Request $request)
    {
        Log::info('[INSTALL] مرحله 2 شروع شد', ['ip' => $request->ip()]);

        try {
            $validated = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8|confirmed',
            ])->validate();

            Log::info('[INSTALL] اعتبارسنجی داده‌های فرم مرحله 2 با موفقیت انجام شد', [
                'email' => $validated['email'],
                'name' => $validated['name']
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('[INSTALL] خطا در اعتبارسنجی فرم مرحله 2', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        Log::info('[INSTALL] در حال ایجاد کاربر ادمین...');
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            Log::info('[INSTALL] کاربر ادمین با موفقیت ایجاد شد', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در ایجاد کاربر ادمین', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['email' => 'خطا در ایجاد کاربر ادمین: ' . $e->getMessage()]);
        }

        Log::info('[INSTALL] در حال ایجاد یا یافتن نقش super-admin...');
        try {
            $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
            Log::info('[INSTALL] نقش super-admin آماده است', [
                'role_id' => $role->id,
                'was_created' => $role->wasRecentlyCreated
            ]);
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در ایجاد نقش super-admin', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['email' => 'خطا در ایجاد نقش: ' . $e->getMessage()]);
        }

        Log::info('[INSTALL] در حال اختصاص نقش به کاربر...');
        try {
            $user->assignRole($role);
            Log::info('[INSTALL] نقش به کاربر اختصاص داده شد');
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در اختصاص نقش به کاربر', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['email' => 'خطا در اختصاص نقش: ' . $e->getMessage()]);
        }

        Log::info('[INSTALL] مرحله 2 با موفقیت تکمیل شد. هدایت به مرحله 3...');
        return redirect()->route('install.step3');
    }

    /**
     * نمایش فرم مرحله 3 (انتخاب تم)
     */
    public function step3()
    {
        Log::info('[INSTALL] نمایش فرم مرحله 3 - در حال بارگذاری لیست تم‌ها...');
        try {
            $themes = Theme::all();
            Log::info('[INSTALL] تم‌ها با موفقیت بارگذاری شدند', [
                'themes_count' => $themes->count()
            ]);
            Log::info('[INSTALL] در حال بارگذاری view install.step3...');
            return view('install.step3', compact('themes'));
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در نمایش فرم مرحله 3', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // دوباره throw می‌کنیم تا exception handler لاگ کند
        }
    }

    /**
     * پردازش فرم مرحله 3
     */
    public function processStep3(Request $request)
    {
        Log::info('[INSTALL] مرحله 3 شروع شد', ['ip' => $request->ip()]);

        try {
            $validated = Validator::make($request->all(), [
                'theme_id' => 'required|exists:themes,id',
            ])->validate();

            Log::info('[INSTALL] اعتبارسنجی داده‌های فرم مرحله 3 با موفقیت انجام شد', [
                'theme_id' => $validated['theme_id']
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('[INSTALL] خطا در اعتبارسنجی فرم مرحله 3', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        Log::info('[INSTALL] در حال یافتن تم انتخابی...', ['theme_id' => $validated['theme_id']]);
        try {
            $theme = Theme::findOrFail($validated['theme_id']);
            Log::info('[INSTALL] تم پیدا شد', [
                'theme_id' => $theme->id,
                'theme_name' => $theme->name
            ]);
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در یافتن تم', [
                'theme_id' => $validated['theme_id'],
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['theme_id' => 'تم انتخاب شده یافت نشد: ' . $e->getMessage()]);
        }

        // 1. فعال کردن تم انتخابی
        Log::info('[INSTALL] در حال غیرفعال کردن تمام تم‌ها...');
        try {
            Theme::query()->update(['active' => false]);
            Log::info('[INSTALL] تمام تم‌ها غیرفعال شدند');
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در غیرفعال کردن تم‌ها', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['theme_id' => 'خطا در غیرفعال کردن تم‌ها: ' . $e->getMessage()]);
        }

        Log::info('[INSTALL] در حال فعال کردن تم انتخابی...');
        try {
            $theme->update(['active' => true]);
            Log::info('[INSTALL] تم انتخابی با موفقیت فعال شد');
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در فعال کردن تم', [
                'theme_id' => $theme->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['theme_id' => 'خطا در فعال کردن تم: ' . $e->getMessage()]);
        }

        // 2. فعال کردن ماژول‌های مورد نیاز تم (فقط در دیتابیس)
        Log::info('[INSTALL] در حال فعال کردن ماژول‌های مورد نیاز تم...');
        try {
            $requiredModules = $theme->requiredModules;
            Log::info('[INSTALL] ماژول‌های مورد نیاز تم', [
                'modules_count' => $requiredModules->count()
            ]);

            foreach ($requiredModules as $module) {
                try {
                    $module->update(['active' => true]);
                    Log::info('[INSTALL] ماژول فعال شد', [
                        'module_id' => $module->id,
                        'module_name' => $module->name
                    ]);
                } catch (\Exception $e) {
                    Log::error('[INSTALL] خطا در فعال کردن ماژول', [
                        'module_id' => $module->id,
                        'module_name' => $module->name ?? 'unknown',
                        'message' => $e->getMessage()
                    ]);
                    // ادامه می‌دهیم و لاگ می‌کنیم اما نصب را متوقف نمی‌کنیم
                }
            }
            Log::info('[INSTALL] فعال‌سازی ماژول‌ها تکمیل شد');
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در پردازش ماژول‌های مورد نیاز تم', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // این خطا بحرانی نیست، ادامه می‌دهیم
        }

        // 3. ایجاد فایل .flag
        Log::info('[INSTALL] در حال ایجاد فایل installed.flag...');
        try {
            $flagPath = storage_path('app/installed.flag');
            $flagContent = 'Installed on: ' . now() . PHP_EOL . 'Theme: ' . $theme->name . ' (ID: ' . $theme->id . ')';
            file_put_contents($flagPath, $flagContent);
            Log::info('[INSTALL] فایل installed.flag با موفقیت ایجاد شد', [
                'path' => $flagPath
            ]);
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در ایجاد فایل installed.flag', [
                'message' => $e->getMessage(),
                'path' => storage_path('app/installed.flag'),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['theme_id' => 'خطا در ایجاد فایل نصب: ' . $e->getMessage()]);
        }

        // 4. پاک کردن کش نهایی
        Log::info('[INSTALL] در حال پاک کردن کش (optimize:clear)...');
        try {
            Artisan::call('optimize:clear');
            $optimizeOutput = Artisan::output();
            Log::info('[INSTALL] کش با موفقیت پاک شد', [
                'output' => $optimizeOutput
            ]);
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در پاک کردن کش', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // این خطا بحرانی نیست، ادامه می‌دهیم
        }

        Log::info('[INSTALL] مرحله 3 با موفقیت تکمیل شد. در حال ورود کاربر ادمین...');
        // 5. هدایت به داشبورد ادمین
        try {
            $admin = User::first();
            if ($admin) {
                auth()->login($admin);
                Log::info('[INSTALL] کاربر ادمین با موفقیت وارد شد', [
                    'user_id' => $admin->id,
                    'email' => $admin->email
                ]);
                return redirect()->route('admin.dashboard')->with('success', 'CRM با موفقیت نصب شد!');
            } else {
                Log::warning('[INSTALL] کاربر ادمین یافت نشد!');
                return redirect()->route('login')->with('error', 'نصب تکمیل شد اما کاربر ادمین یافت نشد. لطفاً وارد شوید.');
            }
        } catch (\Exception $e) {
            Log::error('[INSTALL] خطا در ورود کاربر ادمین', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('login')->with('success', 'نصب با موفقیت تکمیل شد. لطفاً وارد شوید.');
        }
    }
}
