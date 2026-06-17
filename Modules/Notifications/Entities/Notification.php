<?php

namespace Modules\Notifications\Entities;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected $table = 'notifications';

    /**
     * دیکود کردن داده‌های اعلان به آرایه
     */
    public function getFormattedDataAttribute(): array
    {
        return is_array($this->data) ? $this->data : (json_decode($this->data, true) ?: []);
    }
}
