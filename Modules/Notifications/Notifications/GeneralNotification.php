<?php

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;
    public string $message;
    public string $category;
    public string $priority;
    public string $severity;
    public ?string $actionUrl;
    public ?string $icon;
    public array $extraData;
    public array $targetChannels;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $title,
        string $message,
        string $category = 'system',
        string $priority = 'normal',
        string $severity = 'info',
        ?string $actionUrl = null,
        ?string $icon = null,
        array $extraData = [],
        array $targetChannels = ['database']
    ) {
        $this->title          = $title;
        $this->message        = $message;
        $this->category       = $category;
        $this->priority       = $priority;
        $this->severity       = $severity;
        $this->actionUrl      = $actionUrl;
        $this->icon           = $icon;
        $this->extraData      = $extraData;
        $this->targetChannels = $targetChannels;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if (in_array('database', $this->targetChannels, true)) {
            $channels[] = 'database';
        }

        if (in_array('mail', $this->targetChannels, true) && !empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        // اگر هیچ کانالی نبود، حداقل دیتابیس فعال باشد
        return empty($channels) ? ['database'] : $channels;
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        return array_merge([
            'title'      => $this->title,
            'message'    => $this->message,
            'action_url' => $this->actionUrl,
            'category'   => $this->category,
            'priority'   => $this->priority,
            'severity'   => $this->severity,
            'icon'       => $this->icon,
        ], $this->extraData);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('سلام ' . ($notifiable->name ?? 'کاربر گرامی') . '،')
            ->line($this->message);

        if ($this->actionUrl) {
            $mail->action('مشاهده جزئیات در سامانه', url($this->actionUrl));
        }

        $mail->salutation('با تشکر، سامانه مدیریت ارتباط با مشتریان (CRM)');

        return $mail;
    }
}
