<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module as NModule;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. ماژول هسته (مجازی)
        Module::updateOrCreate(
            ['slug' => 'core'],
            [
                'name' => 'ماژول هسته',
                'description' => 'شامل تنظیمات اصلی، داشبورد و قابلیت‌های پایه سیستم.',
                'version' => '1.0.0',
                'active' => true,
                'installed' => true,
                'is_core' => true,
            ]
        );

        // 2. ماژول‌های پیش‌فرض پروژه که باید به صورت اولیه مایگریت و نصب شوند
        $defaultInstalledModules = ['Settings'];

        foreach ($defaultInstalledModules as $name) {
            $slug = Str::lower($name);
            $pModule = class_exists(NModule::class) ? NModule::find($name) : null;
            $version = $pModule?->get('version') ?? '1.0.0';
            $description = $pModule?->get('description') ?? 'تنظیمات عمومی سیستم';

            Module::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'version' => $version,
                    'active' => true,
                    'installed' => true,
                    'installed_at' => now(),
                    'is_core' => false,
                ]
            );

            // اجرای Installer اختصاصی ماژول پیش‌فرض (مایگریشن‌ها و دسترسی‌های اولیه)
            $installerClass = "\\Modules\\{$name}\\Installer";
            if (class_exists($installerClass)) {
                try {
                    $installer = new $installerClass();
                    $installer->install();
                } catch (\Throwable $e) {
                    Log::warning("ModuleSeeder: installer for {$name} encountered an issue: " . $e->getMessage());
                }
            }
        }
    }
}

