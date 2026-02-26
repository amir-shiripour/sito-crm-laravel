<?php

namespace App\Services\Modules;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class ModuleUpdaterService
{
    /**
     * آپدیت امن ماژول از فایل ZIP
     * این متد تضمین می‌کند که داده‌های دیتابیس حفظ شده و فقط کدهای جدید جایگزین شوند.
     */
    public function updateFromZip($moduleName, $zipPath)
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            // ایجاد یک مسیر موقت برای استخراج
            $tempPath = storage_path('app/temp_module_' . Str::random(10));
            $zip->extractTo($tempPath);
            $zip->close();

            // 1. پیدا کردن فایل module.json برای تایید اعتبار
            $jsonFiles = File::glob($tempPath . '/**/module.json');
            if (empty($jsonFiles)) {
                File::deleteDirectory($tempPath);
                return ['success' => false, 'message' => 'ساختار پکیج نامعتبر است (module.json یافت نشد).'];
            }

            $moduleSourceDir = dirname($jsonFiles[0]);
            $moduleData = json_decode(File::get($jsonFiles[0]), true);

            // 2. تایید نام ماژول جهت جلوگیری از آپلود اشتباه روی ماژول دیگر
            if (Str::lower($moduleData['name']) !== Str::lower($moduleName)) {
                File::deleteDirectory($tempPath);
                return ['success' => false, 'message' => 'این پکیج مربوط به ماژول ' . $moduleData['name'] . ' است، نه ' . $moduleName];
            }

            try {
                $targetPath = base_path('Modules/' . $moduleName);

                // 3. تهیه بک‌آپ لحظه‌ای از فایل‌های قبلی (اختیاری اما توصیه شده)
                // در اینجا ما مستقیماً جایگزین می‌کنیم چون File::copyDirectory فایل‌های موجود را اوررایت می‌کند

                // 4. جایگزینی فایل‌های فیزیکی
                File::copyDirectory($moduleSourceDir, $targetPath);
                File::deleteDirectory($tempPath);

                // 5. اجرای مایگریشن‌های جدید ماژول (بدون حذف داده‌های قبلی)
                // پکیج nwidart/laravel-modules فقط فایل‌های اجرا نشده را در دیتابیس ثبت می‌کند.
                Artisan::call('module:migrate', [
                    'module' => $moduleName,
                    '--force' => true
                ]);

                // 6. بازسازی کش سیستم برای شناسایی تغییرات جدید
                Artisan::call('optimize:clear');

                return [
                    'success' => true,
                    'version' => $moduleData['version'] ?? '1.0.0',
                    'message' => "ماژول {$moduleName} با موفقیت به نسخه " . ($moduleData['version'] ?? 'جدید') . " به‌روزرسانی شد."
                ];

            } catch (\Exception $e) {
                Log::error("Module Update Critical Error ($moduleName): " . $e->getMessage());
                return ['success' => false, 'message' => 'خطا در استقرار فایل‌ها یا بروزرسانی دیتابیس: ' . $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'فایل ZIP معتبر نیست یا توسط سرور قابل خواندن نمی‌باشد.'];
    }
}
