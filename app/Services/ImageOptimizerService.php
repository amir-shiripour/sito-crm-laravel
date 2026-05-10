<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageOptimizerService
{
    protected $manager;

    public function __construct()
    {
        // در صورت عدم نصب پکیج intervention/image این سرویس با خطا مواجه نشود و بتواند آپلود عادی را انجام دهد
        if (class_exists(ImageManager::class)) {
            $this->manager = new ImageManager(new Driver());
        }
    }

    /**
     * بهینه‌سازی و آپلود تصویر
     *
     * @param UploadedFile|string $file فایل آپلودی یا مسیر فایل
     * @param string $directory مسیر ذخیره‌سازی
     * @param string $disk دیسک ذخیره‌سازی (پیش‌فرض: public)
     * @param int $maxWidth حداکثر عرض مجاز (پیکسل)
     * @param int $quality کیفیت نهایی (درصد)
     * @return string مسیر ذخیره شده
     */
    public function uploadAndOptimize($file, string $directory, string $disk = 'public', int $maxWidth = 1200, int $quality = 80): string
    {
        if ($file instanceof UploadedFile) {
            $sourcePath = $file->getRealPath() ?: $file->getPathname();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
        } else {
            $sourcePath = $file;
            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $mimeType = mime_content_type($sourcePath) ?: 'application/octet-stream';
        }

        if (empty($sourcePath) || !file_exists($sourcePath)) {
            throw new \Exception('فایل یافت نشد یا مسیر نامعتبر است.');
        }

        // اگر فایل تصویر نباشد یا پکیج نصب نشده باشد، آپلود معمولی انجام می‌شود
        if (!str_starts_with($mimeType, 'image/') || !$this->manager) {
            return $this->normalUpload($sourcePath, $extension, $directory, $disk);
        }

        try {
            // خواندن تصویر
            $image = $this->manager->read($sourcePath);

            // تغییر اندازه هوشمند: حفظ تناسب (Aspect Ratio) و فقط در صورت بزرگتر بودن
            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            // تبدیل به فرمت مدرن WebP با کیفیت تعیین شده برای کاهش حداکثری حجم
            $fileName = Str::random(40) . '.webp';
            $path = $directory . '/' . $fileName;

            $encodedImage = $image->toWebp($quality);

            // ذخیره فایل بهینه شده
            Storage::disk($disk)->put($path, $encodedImage->toString());

            return $path;

        } catch (\Exception $e) {
            // در صورت بروز خطا در پردازش (مثلا فرمت نامعتبر تصویر)، آپلود به صورت عادی ادامه یابد
            return $this->normalUpload($sourcePath, $extension, $directory, $disk);
        }
    }

    /**
     * آپلود معمولی بدون تغییرات (Fall-back)
     */
    private function normalUpload(string $sourcePath, string $extension, string $directory, string $disk): string
    {
        $fileName = Str::random(40) . ($extension ? '.' . $extension : '');
        $path = $directory . '/' . $fileName;

        Storage::disk($disk)->put($path, file_get_contents($sourcePath));

        return $path;
    }
}
