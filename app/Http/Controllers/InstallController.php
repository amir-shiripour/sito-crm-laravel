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
            $envWriter->overwrite('DB_HOST', $validated['db_host']);
            $envWriter->overwrite('DB_PORT', $validated['db_port']);
            $envWriter->overwrite('DB_DATABASE', $validated['db_database']);
            $envWriter->overwrite('DB_USERNAME', $validated['db_username']);
            $password = $validated['db_password'] ? '"' . $validated['db_password'] . '"' : '';
            $envWriter->overwrite('DB_PASSWORD', $password);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['db_database' => 'خطا در نوشتن فایل .env: ' . $e->getMessage()]);
        }

        // 3. پاک کردن کش کانفیگ و اجرای مایگریشن‌ها و سیدرها
        Config::set('database.connections.mysql.host', $validated['db_host']);
        Config::set('database.connections.mysql.port', $validated['db_port']);
        Config::set('database.connections.mysql.database', $validated['db_database']);
        Config::set('database.connections.mysql.username', $validated['db_username']);
        Config::set('database.connections.mysql.password', $validated['db_password']);
        DB::purge('mysql');

        try {
            // اجرای مایگریشن‌ها (ساخت جداول)
            Artisan::call('migrate:fresh', ['--force' => true]);
            // اجرای سیدرها (شامل سیدر اصلاح‌شده ماژول)
            Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        } catch (\Exception $e) {
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
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
            $user->assignRole($role);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['email' => 'خطا در ایجاد کاربر ادمین: ' . $e->getMessage()]);
        }

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
            Theme::query()->update(['active' => false]);
            $theme->update(['active' => true]);

            // 2. فعال کردن ماژول‌های مورد نیاز تم (فقط در دیتابیس)
            foreach ($theme->requiredModules as $module) {
                // ماژول‌های فیزیکی nwidart دیگر در اینجا فعال نمی‌شوند
                // چون ماژول‌های اضافی حذف شده‌اند
                // و ماژول‌های هسته‌ای (مجازی) فقط باید در دیتابیس فعال شوند
                $module->update(['active' => true]);
            }

            // 3. ایجاد فایل .flag
            file_put_contents(storage_path('app/installed.flag'), 'Installed on: ' . now());

            // 4. پاک کردن کش نهایی
            Artisan::call('optimize:clear');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['theme_id' => 'خطا در فعال‌سازی تم: ' . $e->getMessage()]);
        }

        // 5. هدایت به داشبورد ادمین
        $admin = User::first();
        if ($admin) {
            auth()->login($admin);
            return redirect()->route('admin.dashboard')->with('success', 'CRM با موفقیت نصب شد!');
        }

        return redirect()->route('login');
    }
}

