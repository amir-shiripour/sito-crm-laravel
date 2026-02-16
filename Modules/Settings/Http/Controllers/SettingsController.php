<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Settings\Entities\Setting;
use App\Services\GapGPTService;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings::index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/settings'), $filename);
                $value = 'uploads/settings/' . $filename;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    public function testGapGPT(Request $request)
    {
        $request->validate([
            'gapgpt_api_key' => 'required|string',
            'gapgpt_base_url' => 'required|url',
        ]);

        // ایجاد سرویس با تنظیمات موقت (بدون ذخیره در دیتابیس)
        $config = [
            'gapgpt_api_key' => $request->gapgpt_api_key,
            'gapgpt_base_url' => $request->gapgpt_base_url,
            'gapgpt_timeout' => 10, // تایم‌اوت کوتاه برای تست
        ];

        $service = new GapGPTService($config);

        // تلاش برای دریافت لیست مدل‌ها به عنوان تست اتصال
        $models = $service->getModels();

        if ($models && isset($models['data'])) {
            return response()->json([
                'success' => true,
                'message' => 'اتصال با موفقیت برقرار شد. تعداد مدل‌های یافت شده: ' . count($models['data']),
                'models' => array_slice($models['data'], 0, 5) // نمایش ۵ مدل اول برای نمونه
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'خطا در برقراری ارتباط. لطفاً کلید API و آدرس پایه را بررسی کنید.'
        ], 400);
    }
}
