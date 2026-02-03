<?php

namespace Modules\Clients\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function upload(Request $request)
    {
        // بررسی اولیه وجود فایل
        if (!$request->hasFile('csv_file')) {
            return back()->withErrors(['csv_file' => 'هیچ فایلی انتخاب نشده است.']);
        }

        $file = $request->file('csv_file');

        // بررسی اعتبار آپلود
        if (!$file->isValid()) {
            return back()->withErrors(['csv_file' => 'آپلود فایل با مشکل مواجه شد. کد خطا: ' . $file->getError()]);
        }

        // بررسی مسیر موقت فایل
        if (empty($file->getRealPath())) {
            return back()->withErrors(['csv_file' => 'فایل آپلود شد اما مسیر موقت آن یافت نشد. لطفاً تنظیمات upload_tmp_dir در php.ini را بررسی کنید.']);
        }

        try {
            // اعتبارسنجی نوع و حجم فایل
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            // تولید نام یکتا و ذخیره فایل
            $filename = 'import_' . Str::random(20) . '.' . $file->getClientOriginalExtension();

            // ذخیره در پوشه imports در دیسک پیش‌فرض (local)
            $path = $file->storeAs('imports', $filename);

            if (!$path) {
                throw new \Exception('مسیر فایل بازگردانده نشد.');
            }

            // هدایت به صفحه ایمپورت با مسیر فایل
            return redirect()->route('user.settings.clients.import', ['file' => $path]);

        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => 'خطا در ذخیره فایل: ' . $e->getMessage()]);
        }
    }
}
