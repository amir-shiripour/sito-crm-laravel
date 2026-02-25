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
     * آپدیت ماژول از فایل ZIP بدون دست‌کاری داده‌های دیتابیس
     */
    public function updateFromZip($moduleSlug, $zipPath)
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $tempPath = storage_path('app/temp_module_' . Str::random(10));
            $zip->extractTo($tempPath);
            $zip->close();

            // 1. پیدا کردن فایل module.json (ممکن است در روت یا یک پوشه داخل زیپ باشد)
            $jsonFile = File::glob($tempPath . '/**/module.json');
            if (empty($jsonFile)) {
                File::deleteDirectory($tempPath);
                return ['success' => false, 'message' => 'فایل module.json معتبر در پکیج یافت نشد.'];
            }

            $moduleSourceDir = dirname($jsonFile[0]);
            $moduleData = json_decode(File::get($jsonFile[0]), true);

            // 2. تایید نام ماژول برای جلوگیری از اشتباه
            if (Str::lower($moduleData['name']) !== Str::lower($moduleSlug)) {
                File::deleteDirectory($tempPath);
                return ['success' => false, 'message' => 'پکیج مربوط به ماژول دیگری است. نام پکیج: ' . $moduleData['name']];
            }

            try {
                $targetPath = base_path('Modules/' . $moduleData['name']);

                // 3. جایگزینی فایل‌های فیزیکی
                // نکته: File::copyDirectory فایل‌های قبلی را جایگزین می‌کند اما کل پوشه را پاک نمی‌کند.
                File::copyDirectory($moduleSourceDir, $targetPath);
                File::deleteDirectory($tempPath);

                // 4. اجرای مایگریشن‌ها به صورت امن (فقط موارد جدید)
                Artisan::call('module:migrate', ['module' => $moduleData['name'], '--force' => true]);

                // 5. تازه‌سازی کش سیستم
                Artisan::call('optimize:clear');

                return [
                    'success' => true,
                    'version' => $moduleData['version'] ?? '1.0.0',
                    'message' => "ماژول {$moduleData['name']} با موفقیت آپدیت شد."
                ];

            } catch (\Exception $e) {
                Log::error('Module Update Failed: ' . $e->getMessage());
                return ['success' => false, 'message' => 'خطا در فرآیند استقرار: ' . $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'فایل ZIP باز نمی‌شود یا معتبر نیست.'];
    }
}
