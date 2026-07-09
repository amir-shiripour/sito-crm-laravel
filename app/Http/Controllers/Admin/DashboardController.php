<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyImage;

class DashboardController extends Controller
{
    /**
     * نمایش داشبورد اصلی ادمین.
     */
    public function index()
    {
        return view('admin.dashboard', [
            'user' => auth()->user()
        ]);
    }

    /**
     * نمایش گزارش بهینه‌ساز تصویر.
     */
    public function optimizerReport()
    {
        $directories = config('image_optimizer.directories', [
            'properties/covers',
            'properties/gallery',
            'products/masters',
            'products/gallery',
        ]);

        // افزودن خودکار پوشه‌های پویا ماژول ContentForge به لیست گزارش
        if (Storage::disk('public')->exists('content')) {
            $subDirs = Storage::disk('public')->directories('content');
            foreach ($subDirs as $subDir) {
                if (Storage::disk('public')->exists("$subDir/covers")) {
                    $directories[] = "$subDir/covers";
                }
                if (Storage::disk('public')->exists("$subDir/gallery")) {
                    $directories[] = "$subDir/gallery";
                }
            }
        }

        // افزودن خودکار پوشه‌های پویا ماژول نوبت‌دهی (پزشکان) به لیست گزارش
        if (Storage::disk('public')->exists('doctor-media')) {
            $doctorDirs = Storage::disk('public')->directories('doctor-media');
            foreach ($doctorDirs as $docDir) {
                if (Storage::disk('public')->exists("$docDir/photos")) {
                    $directories[] = "$docDir/photos";
                }
            }
        }

        $stats = [];
        $totalOriginalSize = 0;
        $totalOptimizedSize = 0;
        $totalFiles = 0;

        foreach ($directories as $directory) {
            if (!Storage::disk('public')->exists($directory)) {
                continue;
            }

            $files = Storage::disk('public')->files($directory);
            $dirOptimizedCount = 0;
            $dirOptimizedSize = 0;

            foreach ($files as $file) {
                $fileSize = Storage::disk('public')->size($file);
                $totalFiles++;
                $totalOptimizedSize += $fileSize;
                $dirOptimizedSize += $fileSize;

                if (pathinfo($file, PATHINFO_EXTENSION) === 'webp') {
                    $dirOptimizedCount++;
                }
            }

            $dirOriginalSize = $dirOptimizedSize > 0 && $dirOptimizedCount > 0 ? ($dirOptimizedSize / ($dirOptimizedCount / count($files))) / 0.3 : 0;
            $totalOriginalSize += $dirOriginalSize;

            $stats[$directory] = [
                'file_count' => count($files),
                'optimized_count' => $dirOptimizedCount,
                'original_size' => $dirOriginalSize,
                'optimized_size' => $dirOptimizedSize,
                'saved_space' => $dirOriginalSize > $dirOptimizedSize ? $dirOriginalSize - $dirOptimizedSize : 0,
            ];
        }

        return view('admin.optimizer-report', [
            'stats' => $stats,
            'total_original_size' => $totalOriginalSize,
            'total_optimized_size' => $totalOptimizedSize,
            'total_files' => $totalFiles,
            'total_saved' => $totalOriginalSize > $totalOptimizedSize ? $totalOriginalSize - $totalOptimizedSize : 0,
        ]);
    }

    /**
     * اجرای دستی بهینه‌سازی تصاویر قدیمی.
     */
    public function runManualOptimization(Request $request, ImageOptimizerService $optimizer)
    {
        // محدود کردن زمان اجرای اسکریپت برای جلوگیری از Timeout در سرور
        set_time_limit(300);

        $modelsToProcess = [
            Property::class => 'cover_image',
            PropertyImage::class => 'path',
        ];

        if (class_exists(\Modules\ContentForge\App\Models\ContentPost::class)) {
            $modelsToProcess[\Modules\ContentForge\App\Models\ContentPost::class] = 'cover_image';
        }
        if (class_exists(\Modules\ContentForge\App\Models\ContentCategory::class)) {
            $modelsToProcess[\Modules\ContentForge\App\Models\ContentCategory::class] = 'cover_image';
        }
        if (class_exists(\Modules\Market\Entities\MasterProduct::class)) {
            $modelsToProcess[\Modules\Market\Entities\MasterProduct::class] = 'main_image';
        }

        $processedCount = 0;
        $errorCount = 0;

        foreach ($modelsToProcess as $modelClass => $field) {
            $items = $modelClass::where($field, 'not like', '%.webp')
                ->whereNotNull($field)
                ->get();

            foreach ($items as $item) {
                $oldPath = $item->$field;

                if (!$oldPath || !Storage::disk('public')->exists($oldPath)) {
                    continue;
                }

                try {
                    // استفاده از متد جدیدی که خطا را مخفی نمی‌کند و Fallback به آپلود عادی ندارد
                    $result = $optimizer->optimizeExistingImage($oldPath, 'public');

                    if ($result['status'] === 'success') {
                        $item->$field = $result['new_path'];
                        $item->save();
                        $processedCount++;
                    }

                } catch (\Exception $e) {
                    // حالا خطاهای واقعی در لاگ سرور ثبت می‌شوند
                    Log::error("Image optimization failed for {$modelClass} ID {$item->id}: " . $e->getMessage());
                    $errorCount++;
                }
            }
        }

        // بهینه‌سازی گالری تصاویر محصولات
        if (class_exists(\Modules\Market\Entities\MasterProduct::class)) {
            $products = \Modules\Market\Entities\MasterProduct::whereNotNull('gallery_images')->get();
            foreach ($products as $product) {
                $gallery = $product->gallery_images;
                if (!is_array($gallery) || empty($gallery)) {
                    continue;
                }

                $updatedGallery = [];
                $galleryChanged = false;

                foreach ($gallery as $img) {
                    if (is_string($img) && !str_ends_with(strtolower($img), '.webp')) {
                        if (Storage::disk('public')->exists($img)) {
                            try {
                                $result = $optimizer->optimizeExistingImage($img, 'public');
                                if ($result['status'] === 'success') {
                                    $updatedGallery[] = $result['new_path'];
                                    $galleryChanged = true;
                                    $processedCount++;
                                } else {
                                    $updatedGallery[] = $img;
                                }
                            } catch (\Exception $e) {
                                Log::error("Image optimization failed for MasterProduct ID {$product->id} gallery image {$img}: " . $e->getMessage());
                                $errorCount++;
                                $updatedGallery[] = $img;
                            }
                        } else {
                            $updatedGallery[] = $img;
                        }
                    } else {
                        $updatedGallery[] = $img;
                    }
                }

                if ($galleryChanged) {
                    $product->gallery_images = $updatedGallery;
                    $product->save();
                }
            }
        }

        // بهینه‌سازی تصاویر گالری پزشکان در ماژول نوبت‌دهی
        if (class_exists(\Modules\Booking\App\Models\DoctorMedia::class)) {
            $photos = \Modules\Booking\App\Models\DoctorMedia::where('type', 'photo')
                ->where('file_path', 'not like', '%.webp')
                ->whereNotNull('file_path')
                ->get();

            foreach ($photos as $photo) {
                $oldPath = $photo->file_path;
                if (!$oldPath || !Storage::disk('public')->exists($oldPath)) {
                    continue;
                }

                try {
                    $result = $optimizer->optimizeExistingImage($oldPath, 'public');
                    if ($result['status'] === 'success') {
                        $photo->file_path = $result['new_path'];
                        $photo->save();
                        $processedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Image optimization failed for DoctorMedia ID {$photo->id}: " . $e->getMessage());
                    $errorCount++;
                }
            }
        }

        // اگر خطایی رخ داد، به کاربر هشدار می‌دهیم تا لاگ‌ها را چک کند
        $message = "عملیات با پایان رسید. {$processedCount} تصویر بهینه شد.";
        if ($errorCount > 0) {
            $message .= " متاسفانه {$errorCount} تصویر با خطا مواجه شد. لطفاً فایل laravel.log را بررسی کنید.";
            return back()->with('warning', $message); // پیشنهاد می‌شود یک Alert زرد/قرمز برای Warning داشته باشید
        }

        return back()->with('success', $message);
    }
}
