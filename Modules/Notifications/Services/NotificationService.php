<?php

namespace Modules\Notifications\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Modules\Notifications\Entities\NotificationPreference;
use Modules\Notifications\Entities\NotificationUserSetting;
use Modules\Notifications\Notifications\GeneralNotification;

class NotificationService
{
    /**
     * ارسال یک اعلان به یک یا چند کاربر با رعایت ترجیحات و کانال‌های انتخابی
     *
     * @param mixed $recipients (User | Collection | array | int)
     * @param string $title
     * @param string $message
     * @param string $category (tasks, reminders, clients, sales, system, broadcast, workflows)
     * @param string $priority (low, normal, high, urgent)
     * @param string $severity (info, success, warning, danger)
     * @param string|null $actionUrl
     * @param array $channels کانال‌های مجاز (اگر خالی باشد از ترجیحات کاربر یا پیش‌فرض سیستم خوانده می‌شود)
     * @param array $options تنظیمات اضافه مانند sms_template و extra_data
     * @return int تعداد کاربران دریافت‌کننده
     */
    public function send(
        mixed $recipients,
        string $title,
        string $message,
        string $category = 'system',
        string $priority = 'normal',
        string $severity = 'info',
        ?string $actionUrl = null,
        array $channels = [],
        array $options = []
    ): int {
        $users = $this->resolveRecipients($recipients);
        if ($users->isEmpty()) {
            return 0;
        }

        $defaultChannels = config('notifications.default_channels.' . $category, ['database']);
        $processedCount = 0;

        foreach ($users as $user) {
            if (!$user instanceof User) {
                continue;
            }

            // تعیین کانال‌های هدف برای این کاربر
            $targetChannels = $this->resolveChannelsForUser($user, $category, $channels, $defaultChannels);

            // بررسی ساعات سکوت (Quiet Hours) برای کانال‌های مزاحم مثل پیامک
            $inQuietHours = $this->isUserInQuietHours($user);
            if ($inQuietHours && $priority !== 'urgent') {
                $targetChannels = array_diff($targetChannels, ['sms']);
            }

            // ۱. ارسال اعلان لاراولی (Database و Mail)
            $laravelChannels = array_intersect($targetChannels, ['database', 'mail']);
            if (!empty($laravelChannels)) {
                try {
                    $notification = new GeneralNotification(
                        title: $title,
                        message: $message,
                        category: $category,
                        priority: $priority,
                        severity: $severity,
                        actionUrl: $actionUrl,
                        icon: $options['icon'] ?? null,
                        extraData: $options['extra_data'] ?? [],
                        targetChannels: $laravelChannels
                    );

                    NotificationFacade::send($user, $notification);
                } catch (\Throwable $e) {
                    Log::error("[NotificationService] Error sending in-app notification to user {$user->id}: " . $e->getMessage());
                }
            }

            // ۲. ارسال پیامک در صورت فعال بودن کانال SMS و وجود شماره همراه
            if (in_array('sms', $targetChannels, true) && !empty($user->mobile)) {
                $this->sendSmsNotification($user, $title, $message, $options);
            }

            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * ارسال پیامک اطلاع‌رسانی از طریق ماژول SMS
     */
    protected function sendSmsNotification(User $user, string $title, string $message, array $options): void
    {
        try {
            if (class_exists(\Modules\Sms\Services\SmsManager::class)) {
                $smsManager = app(\Modules\Sms\Services\SmsManager::class);
                $smsText = "{$title}\n{$message}\n" . config('app.name');
                
                // در صورتی که قالب مشخص شده باشد
                if (!empty($options['sms_template']) && method_exists($smsManager, 'sendPattern')) {
                    $smsManager->sendPattern($user->mobile, $options['sms_template'], $options['sms_params'] ?? []);
                } elseif (method_exists($smsManager, 'send')) {
                    $smsManager->send($user->mobile, $smsText);
                }
            } elseif (class_exists(\Modules\Sms\Facades\Sms::class)) {
                $smsText = "{$title}\n{$message}\n" . config('app.name');
                \Modules\Sms\Facades\Sms::send($user->mobile, $smsText);
            }
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Error sending SMS to {$user->mobile}: " . $e->getMessage());
        }
    }

    /**
     * حل ترجیحات کانال‌های کاربر
     */
    protected function resolveChannelsForUser(User $user, string $category, array $explicitChannels, array $defaultChannels): array
    {
        // اگر فرستنده صراحتاً کانال‌ها را مشخص کرده باشد
        if (!empty($explicitChannels)) {
            return $explicitChannels;
        }

        // جستجو در تنظیمات ثبت شده کاربر
        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('category', $category)
            ->first();

        if ($preference) {
            $channels = [];
            if ($preference->channel_database) $channels[] = 'database';
            if ($preference->channel_sms) $channels[] = 'sms';
            if ($preference->channel_mail) $channels[] = 'mail';
            return empty($channels) ? ['database'] : $channels;
        }

        return $defaultChannels;
    }

    /**
     * بررسی آیا کاربر در ساعات سکوت قرار دارد یا خیر
     */
    protected function isUserInQuietHours(User $user): bool
    {
        $setting = NotificationUserSetting::where('user_id', $user->id)->first();
        if (!$setting) {
            return false;
        }

        return $setting->isInQuietHours();
    }

    /**
     * تبدیل انواع ورودی گیرندگان به کالکشن مدل User
     */
    protected function resolveRecipients(mixed $recipients): Collection
    {
        if ($recipients instanceof User) {
            return collect([$recipients]);
        }

        if ($recipients instanceof Collection) {
            return $recipients;
        }

        if (is_array($recipients)) {
            if (empty($recipients)) {
                return collect();
            }

            if ($recipients[0] instanceof User) {
                return collect($recipients);
            }

            return User::whereIn('id', $recipients)->get();
        }

        if (is_numeric($recipients)) {
            $user = User::find($recipients);
            return $user ? collect([$user]) : collect();
        }

        return collect();
    }
}
