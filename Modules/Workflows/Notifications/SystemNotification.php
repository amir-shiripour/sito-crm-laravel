<?php

namespace Modules\Workflows\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected ?string $actionUrl;

    public function __construct(string $title, string $message, ?string $actionUrl = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'      => $this->title,
            'message'    => $this->message,
            'action_url' => $this->actionUrl,
        ];
    }
}
