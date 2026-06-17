<?php

namespace Modules\Reminders\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Settings\Entities\Setting;
use Spatie\Permission\Models\Role;

class ReminderSettingsController extends Controller
{
    /**
     * نمایش صفحه تنظیمات یادآوری و تعویق.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        // دیکود کردن نقش‌ها و کاربران ارجاع
        $escalationRoles = isset($settings['reminders_escalation_roles']) 
            ? (json_decode($settings['reminders_escalation_roles'], true) ?: []) 
            : [];

        $escalationUsers = isset($settings['reminders_escalation_users']) 
            ? (json_decode($settings['reminders_escalation_users'], true) ?: []) 
            : [];

        $roles = Role::all();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('reminders::user.settings.index', compact(
            'settings',
            'escalationRoles',
            'escalationUsers',
            'roles',
            'users'
        ));
    }

    /**
     * ذخیره تنظیمات یادآوری و تعویق.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'reminders_in_progress_enabled'    => 'nullable|string',
            'reminders_snooze_enabled'         => 'nullable|string', // input checkbox sends value or null
            'reminders_snooze_limit'           => 'required|integer|min:1|max:100',
            'reminders_snooze_reason_required' => 'required|string|in:optional,required,disabled',
            'reminders_escalation_roles'       => 'nullable|string',
            'reminders_escalation_users'       => 'nullable|string',
        ]);

        // ذخیره فیلد فعال بودن درحال انجام
        Setting::updateOrCreate(
            ['key' => 'reminders_in_progress_enabled'],
            ['value' => isset($data['reminders_in_progress_enabled']) ? '1' : '0']
        );

        // ذخیره فیلد فعال بودن تعویق
        Setting::updateOrCreate(
            ['key' => 'reminders_snooze_enabled'],
            ['value' => isset($data['reminders_snooze_enabled']) ? '1' : '0']
        );

        // ذخیره حد مجاز تعویق
        Setting::updateOrCreate(
            ['key' => 'reminders_snooze_limit'],
            ['value' => $data['reminders_snooze_limit']]
        );

        // ذخیره وضعیت اجباری بودن دلیل
        Setting::updateOrCreate(
            ['key' => 'reminders_snooze_reason_required'],
            ['value' => $data['reminders_snooze_reason_required']]
        );

        // ذخیره نقش‌های Escalation
        Setting::updateOrCreate(
            ['key' => 'reminders_escalation_roles'],
            ['value' => $data['reminders_escalation_roles'] ?? '[]']
        );

        // ذخیره کاربران Escalation
        Setting::updateOrCreate(
            ['key' => 'reminders_escalation_users'],
            ['value' => $data['reminders_escalation_users'] ?? '[]']
        );

        return redirect()->back()->with('success', 'تنظیمات یادآوری و تعویق با موفقیت بروزرسانی شد.');
    }
}
