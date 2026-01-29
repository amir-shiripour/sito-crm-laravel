<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertySetting;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $currency = PropertySetting::get('currency', 'toman');
        $max_file_size = PropertySetting::get('max_file_size', 10240);
        $max_gallery_images = PropertySetting::get('max_gallery_images', 10);
        $allowed_file_types = PropertySetting::get('allowed_file_types', 'jpeg,png,jpg,gif');

        // Video Settings
        $max_video_size = PropertySetting::get('max_video_size', 20480); // 20MB default
        $allowed_video_types = PropertySetting::get('allowed_video_types', 'mp4,mov,avi');

        // Code Settings
        $property_code_prefix = PropertySetting::get('property_code_prefix', 'P');
        $property_code_separator = PropertySetting::get('property_code_separator', '-');
        $property_code_include_year = PropertySetting::get('property_code_include_year', 1);

        // Storage Report
        $storagePath = 'properties';
        $totalSize = 0;
        $fileCount = 0;

        if (Storage::disk('public')->exists($storagePath)) {
            $files = Storage::disk('public')->allFiles($storagePath);
            $fileCount = count($files);
            foreach ($files as $file) {
                $totalSize += Storage::disk('public')->size($file);
            }
        }

        $formattedSize = $this->formatBytes($totalSize);

        return view('properties::user.settings.index', compact(
            'currency',
            'max_file_size',
            'max_gallery_images',
            'allowed_file_types',
            'max_video_size',
            'allowed_video_types',
            'property_code_prefix',
            'property_code_separator',
            'property_code_include_year',
            'formattedSize',
            'fileCount'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'currency' => 'required|in:toman,rial',
            'max_file_size' => 'required|integer|min:1024',
            'max_gallery_images' => 'required|integer|min:1|max:50',
            'allowed_file_types' => 'required|string',
            'max_video_size' => 'required|integer|min:1024',
            'allowed_video_types' => 'required|string',
            'property_code_prefix' => 'nullable|string|max:10',
            'property_code_separator' => 'nullable|string|max:5',
            'property_code_include_year' => 'nullable|boolean',
        ]);

        $allowedFileTypes = str_replace(' ', '', $request->allowed_file_types);
        $allowedVideoTypes = str_replace(' ', '', $request->allowed_video_types);

        PropertySetting::set('currency', $request->currency);
        PropertySetting::set('max_file_size', $request->max_file_size);
        PropertySetting::set('max_gallery_images', $request->max_gallery_images);
        PropertySetting::set('allowed_file_types', $allowedFileTypes);

        PropertySetting::set('max_video_size', $request->max_video_size);
        PropertySetting::set('allowed_video_types', $allowedVideoTypes);

        PropertySetting::set('property_code_prefix', $request->property_code_prefix);
        PropertySetting::set('property_code_separator', $request->property_code_separator);
        PropertySetting::set('property_code_include_year', $request->has('property_code_include_year') ? 1 : 0);

        return back()->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
