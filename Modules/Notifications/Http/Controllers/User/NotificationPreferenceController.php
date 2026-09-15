<?php

namespace Modules\Notifications\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Notifications\Entities\NotificationPreference;
use Modules\Notifications\Entities\NotificationUserSetting;

class NotificationPreferenceController extends Controller
{
    /**
     * نمایش فرم تنظیمات و ترجیحات اعلان‌های کاربر
     */
    public function index()
    {
        $user = Auth::user();
        $categories = config('notifications.categories', []);

        // دریافت ترجیحات ثبت شده کاربر
        $userPreferences = NotificationPreference::where('user_id', $user->id)
            ->get()
            ->keyBy('category');

        // تنظیمات عمومی (صدا و ساعات سکوت)
        $userSetting = NotificationUserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'sound_enabled'       => true,
                'quiet_hours_enabled' => false,
                'quiet_hours_start'   => '22:00:00',
                'quiet_hours_end'     => '08:00:00',
            ]
        );

        $defaultChannels = config('notifications.default_channels', []);

        return view('notifications::user.settings', compact(
            'user',
            'categories',
            'userPreferences',
            'userSetting',
            'defaultChannels'
        ));
    }

    /**
     * ذخیره‌سازی ترجیحات اعلان‌های کاربر
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'preferences'          => 'nullable|array',
            'sound_enabled'        => 'nullable|boolean',
            'quiet_hours_enabled'  => 'nullable|boolean',
            'quiet_hours_start'    => 'nullable|string',
            'quiet_hours_end'      => 'nullable|string',
        ]);

        $categories = config('notifications.categories', []);
        $preferencesInput = $request->input('preferences', []);

        // به‌روزرسانی ماتریس کانال‌ها به ازای هر کتگوری
        foreach ($categories as $catKey => $catData) {
            $prefData = $preferencesInput[$catKey] ?? [];

            NotificationPreference::updateOrCreate(
                [
                    'user_id'  => $user->id,
                    'category' => $catKey,
                ],
                [
                    'channel_database' => isset($prefData['database']),
                    'channel_sms'      => isset($prefData['sms']),
                    'channel_mail'     => isset($prefData['mail']),
                ]
            );
        }

        // به‌روزرسانی تنظیمات عمومی
        NotificationUserSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'sound_enabled'       => $request->boolean('sound_enabled', false),
                'quiet_hours_enabled' => $request->boolean('quiet_hours_enabled', false),
                'quiet_hours_start'   => $request->input('quiet_hours_start', '22:00:00'),
                'quiet_hours_end'     => $request->input('quiet_hours_end', '08:00:00'),
            ]
        );

        return back()->with('success', 'تنظیمات و ترجیحات اعلان‌ها با موفقیت ذخیره شدند.');
    }
}
