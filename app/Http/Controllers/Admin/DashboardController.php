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
        ]);

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

        // اگر خطایی رخ داد، به کاربر هشدار می‌دهیم تا لاگ‌ها را چک کند
        $message = "عملیات با پایان رسید. {$processedCount} تصویر بهینه شد.";
        if ($errorCount > 0) {
            $message .= " متاسفانه {$errorCount} تصویر با خطا مواجه شد. لطفاً فایل laravel.log را بررسی کنید.";
            return back()->with('warning', $message); // پیشنهاد می‌شود یک Alert زرد/قرمز برای Warning داشته باشید
        }

        return back()->with('success', $message);
    }
}
