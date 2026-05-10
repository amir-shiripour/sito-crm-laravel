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
        $directories = [
            'properties/covers',
            'properties/gallery',
            // Add other relevant directories here
        ];

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
        $modelsToProcess = [
            Property::class => 'cover_image',
            PropertyImage::class => 'path',
        ];

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
                    $fullOldPath = Storage::disk('public')->path($oldPath);
                    $directory = dirname($oldPath);

                    $newPath = $optimizer->uploadAndOptimize($fullOldPath, $directory);

                    if ($newPath && $newPath !== $oldPath) {
                        $item->$field = $newPath;
                        $item->save();

                        // Delete the old file after successful optimization and DB update
                        Storage::disk('public')->delete($oldPath);

                        $processedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Image optimization failed for {$modelClass} ID {$item->id}: " . $e->getMessage());
                    $errorCount++;
                }
            }
        }

        return back()->with('success', "عملیات با موفقیت انجام شد. {$processedCount} تصویر بهینه و {$errorCount} تصویر با خطا مواجه شد.");
    }
}
