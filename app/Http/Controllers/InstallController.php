<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\User;
use App\Services\EnvWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Config;
use IlluminateRequest;

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
        $validated = Validator::make($request->all(), [
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ])->validate();

        // 1. تست اتصال به دیتابیس
        $dsn = "mysql:host={$validated['db_host']};port={$validated['db_port']};dbname={$validated['db_database']}";
        try {
            new \PDO($dsn, $validated['db_username'], $validated['db_password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
        } catch (\PDOException $e) {
            return back()->withInput()->withErrors(['db_database' => 'اطلاعات دیتابیس صحیح نیست. خطای اتصال: ' . $e->getMessage()]);
        }

        // 2. نوشتن اطلاعات در فایل .env
        try {
            $envWriter = new EnvWriter();

            // --- اصلاح شد: نام متد از setEnv به overwrite تغییر کرد ---
            $envWriter->overwrite('DB_HOST', $validated['db_host']);
            $envWriter->overwrite('DB_PORT', $validated['db_port']);
            $envWriter->overwrite('DB_DATABASE', $validated['db_database']);
            $envWriter->overwrite('DB_USERNAME', $validated['db_username']);
            // اطمینان از قرار گرفتن پسورد در کوتیشن برای جلوگیری از خطا
            $password = $validated['db_password'] ? '"' . $validated['db_password'] . '"' : '';
            $envWriter->overwrite('DB_PASSWORD', $password);
            // --- پایان اصلاح ---

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['db_database' => 'خطا در نوشتن فایل .env: ' . $e->getMessage()]);
        }

        // 3. پاک کردن کش کانفیگ و اجرای مایگریشن‌ها و سیدرها
        // ما باید کانفیگ دیتابیس را برای ادامه ریکوئست به روز کنیم
        Config::set('database.connections.mysql.host', $validated['db_host']);
        Config::set('database.connections.mysql.port', $validated['db_port']);
        Config::set('database.connections.mysql.database', $validated['db_database']);
        Config::set('database.connections.mysql.username', $validated['db_username']);
        Config::set('database.connections.mysql.password', $validated['db_password']);

        DB::purge('mysql');

        try {
            // اجرای مایگریشن‌ها (ساخت جداول)
            Artisan::call('migrate:fresh', ['--force' => true]);

            // اجرای سیدرهای اصلی، ماژول و تم
            Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);

        } catch (\Exception $e) {
            // اگر خطا رخ داد، .env را به حالت قبل برگردانیم (اختیاری)
            // $envWriter->overwrite('DB_DATABASE', '');
            return back()->withInput()->withErrors(['db_database' => 'خطا در اجرای مایگریشن‌ها: ' . $e->getMessage()]);
        }

        // 4. هدایت به مرحله 2
        return redirect()->route('install.step2');
    }

    /**
     * نمایش فرم مرحله 2 (ایجاد ادمین)
     */
    public function step2()
    {
        return view('install.step2');
    }

    /**
     * پردازش فرم مرحله 2
     */
    public function processStep2(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ])->validate();

        try {
            // 1. ایجاد کاربر
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2. ایجاد نقش Super Admin (اگر وجود ندارد)
            $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

            // 3. اختصاص نقش به کاربر
            $user->assignRole($role);

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['email' => 'خطا در ایجاد کاربر ادمین: ' . $e->getMessage()]);
        }

        // 4. هدایت به مرحله 3 (انتخاب تم)
        return redirect()->route('install.step3');
    }

    /**
     * نمایش فرم مرحله 3 (انتخاب تم)
     */
    public function step3()
    {
        $themes = Theme::all();
        return view('install.step3', compact('themes'));
    }

    /**
     * پردازش فرم مرحله 3
     */
    public function processStep3(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'theme_id' => 'required|exists:themes,id',
        ])->validate();

        try {
            $theme = Theme::findOrFail($validated['theme_id']);

            // 1. فعال کردن تم انتخابی
            Theme::query()->update(['active' => false]); // غیرفعال کردن همه تم‌ها
            $theme->update(['active' => true]); // فعال کردن تم انتخابی

            // 2. فعال کردن تمام ماژول‌های مورد نیاز این تم
            foreach ($theme->requiredModules as $module) {
                $module->update(['active' => true]);
            }

            // 3. فعال کردن ماژول هسته (Core)
            \App\Models\Module::where('slug', 'core')->update(['active' => true]);

            // 4. ایجاد فایل .flag
            file_put_contents(storage_path('app/installed.flag'), 'Installed on: ' . now());

            // 5. پاک کردن کش نهایی
            Artisan::call('optimize:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['theme_id' => 'خطا در فعال‌سازی تم: ' . $e->getMessage()]);
        }

        // 6. هدایت به داشبورد ادمین
        // ما باید کاربر ادمین را که در مرحله قبل ساختیم لاگین کنیم
        $admin = User::first(); // اولین کاربر، ادمین است
        if ($admin) {
            auth()->login($admin);
            return redirect()->route('admin.dashboard')->with('success', 'CRM با موفقیت نصب شد!');
        }

        return redirect()->route('login');
    }
}

