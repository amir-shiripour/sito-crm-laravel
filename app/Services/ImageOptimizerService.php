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
        if (class_exists(ImageManager::class) && extension_loaded('gd')) {
            $this->manager = new ImageManager(new Driver());
        }
    }

    /**
     * بهینه‌سازی و آپلود تصویر (مخصوص آپلودهای جدید از فرم)
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
            $image = $this->manager->read($sourcePath);

            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            $fileName = Str::random(40) . '.webp';
            $path = $directory . '/' . $fileName;

            $encodedImage = $image->toWebp($quality);
            Storage::disk($disk)->put($path, $encodedImage->toString());

            return $path;

        } catch (\Exception $e) {
            return $this->normalUpload($sourcePath, $extension, $directory, $disk);
        }
    }

    /**
     * بهینه‌سازی فایل‌های قدیمی سرور (مخصوص عملیات دسته‌ای - Batch Processing)
     * این متد هرگز فایل را به صورت مخفیانه دور نمی‌زند. یا درست کار می‌کند یا خطا می‌دهد.
     */
    public function optimizeExistingImage(string $filePath, string $disk = 'public', int $maxWidth = 1200, int $quality = 80): array
    {
        if (!$this->manager) {
            throw new \Exception('پکیج Intervention Image نصب نیست یا اکستنشن GD روی سرور فعال نیست.');
        }

        $fullPath = Storage::disk($disk)->path($filePath);

        if (!file_exists($fullPath)) {
            throw new \Exception('فایل در مسیر یافت نشد.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        if (!str_starts_with($mimeType, 'image/')) {
            throw new \Exception('فایل انتخابی تصویر نیست.');
        }

        // اگر از قبل WebP است نیازی به پردازش ندارد
        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'webp') {
            return ['status' => 'skipped'];
        }

        // در عملیات اصلی ما خطا را مخفی نمی‌کنیم تا دلیل اصلی در لاگ سرور ثبت شود
        $image = $this->manager->read($fullPath);

        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $encodedImage = $image->toWebp($quality);

        $directory = dirname($filePath);
        $filenameWithoutExt = pathinfo($filePath, PATHINFO_FILENAME);
        $newFilePath = ($directory === '.' ? '' : $directory . '/') . $filenameWithoutExt . '.webp';

        // ذخیره فایل جدید
        Storage::disk($disk)->put($newFilePath, $encodedImage->toString());

        // پاک کردن فایل قدیمی در صورت متفاوت بودن نام
        if ($filePath !== $newFilePath) {
            Storage::disk($disk)->delete($filePath);
        }

        return [
            'status' => 'success',
            'new_path' => $newFilePath
        ];
    }

    /**
     * آپلود معمولی بدون تغییرات
     */
    private function normalUpload(string $sourcePath, string $extension, string $directory, string $disk): string
    {
        $fileName = Str::random(40) . ($extension ? '.' . $extension : '');
        $path = $directory . '/' . $fileName;
        Storage::disk($disk)->put($path, file_get_contents($sourcePath));
        return $path;
    }
}
