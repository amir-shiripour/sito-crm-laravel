<?php

namespace Modules\Reminders\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Reminders\Entities\Reminder;

class SnoozeEscalationNotification extends Notification
{
    use Queueable;

    protected Reminder $reminder;
    protected string $title;
    protected string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
        $userName = $reminder->user ? $reminder->user->name : 'ناشناس';
        $title = $reminder->relatedTitle();

        $this->title = '⚠️ ارجاع خودکار یادآوری تعویق‌شده';
        $this->message = "یادآوری «{$title}» متعلق به کاربر «{$userName}» به علت تعویق مکرر ({$reminder->snooze_count} بار) ارجاع داده شد.";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'       => $this->title,
            'message'     => $this->message,
            'reminder_id' => $this->reminder->id,
            'action_url'  => $this->reminder->relatedUrl(),
        ];
    }
}
