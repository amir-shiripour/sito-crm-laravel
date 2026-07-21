<?php

namespace Modules\ContentForge\Database\Seeders;

use Illuminate\Database\Seeder;

class ContentForgeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // جهت سازگاری با دستور reset و module:seed، متدهای سید موجودیت و تنظیمات را مستقیماً فراخوانی می‌کنیم
        $installer = new \Modules\ContentForge\Installer();
        
        // از بازتاب (Reflection) یا تغییر دسترسی متدهای غیرعمومی برای اجرای سید استفاده می‌کنیم
        $reflector = new \ReflectionClass($installer);
        
        $seedDefaultEntity = $reflector->getMethod('seedDefaultEntity');
        $seedDefaultEntity->setAccessible(true);
        $seedDefaultEntity->invoke($installer);

        $seedDefaultSettings = $reflector->getMethod('seedDefaultSettings');
        $seedDefaultSettings->setAccessible(true);
        $seedDefaultSettings->invoke($installer);

        $publishDefaultThemeFiles = $reflector->getMethod('publishDefaultThemeFiles');
        $publishDefaultThemeFiles->setAccessible(true);
        $publishDefaultThemeFiles->invoke($installer);
    }
}
