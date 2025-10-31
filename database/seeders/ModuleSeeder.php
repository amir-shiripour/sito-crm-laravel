<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Core Module (Always active)
        Module::updateOrCreate(['slug' => 'core'], [
            'name' => 'هسته اصلی',
            'description' => 'شامل تنظیمات پایه و هسته سیستم.',
            'version' => '1.0.0',
            'active' => true,
        ]);

        // 2. User Management (by Admin)
        Module::updateOrCreate(['slug' => 'users'], [
            'name' => 'مدیریت کاربران',
            'description' => 'اجازه می‌دهد ادمین کاربران را ویرایش کند.',
            'version' => '1.0.0',
            'active' => true, // We assume this is active by default
        ]);

        // 3. Profile Management (by User)
        Module::updateOrCreate(['slug' => 'profile'], [
            'name' => 'مدیریت پروفایل',
            'description' => 'اجازه می‌دهد کاربر پروفایل خود را ویرایش کند (Jetstream).',
            'version' => '1.0.0',
            'active' => true,
        ]);

        // 4. Module Management (by Super Admin)
        Module::updateOrCreate(['slug' => 'modules'], [
            'name' => 'مدیریت ماژول‌ها',
            'description' => 'اجازه می‌دهد ادمین ماژول‌ها را فعال/غیرفعال کند.',
            'version' => '1.0.0',
            'active' => true,
        ]);

        // 5. Theme Management (by Super Admin)
        Module::updateOrCreate(['slug' => 'themes'], [
            'name' => 'مدیریت تم‌ها',
            'description' => 'اجازه می‌دهد ادمین تم‌ها را مدیریت و فعال کند.',
            'version' => '1.0.0',
            'active' => true,
        ]);

        // --- Add other optional modules here ---
        // Example: Blog Module (inactive by default)
        Module::updateOrCreate(['slug' => 'blog'], [
            'name' => 'وبلاگ',
            'description' => 'قابلیت افزودن وبلاگ به سایت.',
            'version' => '1.0.0',
            'active' => false,
        ]);

        // Example: E-commerce (inactive by default)
        Module::updateOrCreate(['slug' => 'shop'], [
            'name' => 'فروشگاه',
            'description' => 'قابلیت فروش محصول در سایت.',
            'version' => '1.0.0',
            'active' => false,
        ]);
    }
}

