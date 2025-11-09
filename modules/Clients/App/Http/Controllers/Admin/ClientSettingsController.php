<?php

namespace Modules\Clients\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client; // مدل ماژول
use Illuminate\Support\Facades\Storage;

class ClientSettingsController extends Controller
{
    // نمایش فرم تنظیمات ماژول (مثل labelها)
    public function edit()
    {
        // تنظیمات می‌تواند از DB خوانده شود (clients_settings) یا از config ماژول
        // اگر از جدول use: Modules\Clients\Models\ClientSetting (یا یک مدل ساده)
        $settings = config('clients.settings', [
            'label' => 'Client',
            'plural_label' => 'Clients'
        ]);

        // اگر از جدول تنظیمات استفاده می‌کنید، بارگزاری کنید:
        if (\Schema::hasTable('clients_settings')) {
            $rows = \DB::table('clients_settings')->pluck('value', 'key')->toArray();
            $settings = array_merge($settings, $rows);
        }

        return view('admin.clients.settings', compact('settings'));
    }

    // ذخیره تنظیمات
    public function update(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:50',
            'plural_label' => 'required|string|max:50',
            // سایر فیلدهای تنظیمات...
        ]);

        // ذخیره در جدول clients_settings (convenience)
        if (! \Schema::hasTable('clients_settings')) {
            // اگر جدول نیست هدایت کنید یا ایجاد کنید (در محیط production از migration استفاده کنید)
            return back()->with('error', 'جدول settings برای ماژول clients موجود نیست.');
        }

        foreach ($data as $key => $value) {
            \DB::table('clients_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // پاکسازی کش و بازخوانی config در صورت نیاز
        cache()->forget('clients.settings');

        return back()->with('success', 'تنظیمات ذخیره شد.');
    }
}
