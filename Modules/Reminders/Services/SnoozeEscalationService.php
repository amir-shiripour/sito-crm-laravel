<?php

namespace Modules\Reminders\Services;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Modules\Reminders\Entities\Reminder;
use Modules\Reminders\Notifications\SnoozeEscalationNotification;

class SnoozeEscalationService
{
    /**
     * بررسی تعویق مکرر و اجرای Escalation در صورت لزوم.
     */
    public function checkAndEscalate(Reminder $reminder): bool
    {
        $enabled = get_setting('reminders_snooze_enabled', '1') == '1';
        if (!$enabled) {
            return false;
        }

        $limit = (int) get_setting('reminders_snooze_limit', 5);
        if ($reminder->snooze_count < $limit) {
            return false;
        }

        // تغییر وضعیت یادآوری به ارجاع شده
        $reminder->status = Reminder::STATUS_ESCALATED;
        $reminder->save();

        // پیدا کردن کاربران گیرنده اعلان
        $recipientUserIds = [];

        // ۱. دریافت کاربران خاص از تنظیمات
        $configUsers = get_setting('reminders_escalation_users');
        if ($configUsers) {
            $userIds = json_decode($configUsers, true) ?: [];
            $recipientUserIds = array_merge($recipientUserIds, $userIds);
        }

        // ۲. دریافت کاربران بر اساس نقش‌ها از تنظیمات
        $configRoles = get_setting('reminders_escalation_roles');
        if ($configRoles) {
            $roleNames = json_decode($configRoles, true) ?: [];
            if (!empty($roleNames)) {
                $roleUsers = User::role($roleNames)->pluck('id')->toArray();
                $recipientUserIds = array_merge($recipientUserIds, $roleUsers);
            }
        }

        $recipientUserIds = array_unique($recipientUserIds);

        if (!empty($recipientUserIds)) {
            $recipients = User::whereIn('id', $recipientUserIds)->get();
            NotificationFacade::send($recipients, new SnoozeEscalationNotification($reminder));
        }

        // ثبت لاگ فعالیت Escalation در هسته سیستم
        $userName = $reminder->user ? $reminder->user->name : 'کاربر نامشخص';
        $title = $reminder->relatedTitle();
        ActivityLogger::log(
            'escalate_reminder',
            "یادآوری «{$title}» متعلق به «{$userName}» به علت تعویق بیش از حد ({$reminder->snooze_count} بار) به مدیریت ارجاع داده شد.",
            $reminder,
            [
                'snooze_count' => $reminder->snooze_count,
                'recipients'   => $recipientUserIds
            ]
        );

        return true;
    }
}
