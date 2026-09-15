<?php

namespace Modules\Notifications\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected $table = 'notifications';

    /**
     * دیکود کردن داده‌های اعلان به آرایه (جهت سازگاری با کدهای موجود)
     */
    public function getFormattedDataAttribute(): array
    {
        return is_array($this->data) ? $this->data : (json_decode($this->data, true) ?: []);
    }

    /**
     * عنوان اعلان
     */
    public function getTitleAttribute(): string
    {
        $data = $this->formatted_data;
        return $data['title'] ?? 'اعلان سیستم';
    }

    /**
     * متن پیام اعلان
     */
    public function getMessageAttribute(): string
    {
        $data = $this->formatted_data;
        return $data['message'] ?? $data['description'] ?? '';
    }

    /**
     * لینک اقدام مرتبط
     */
    public function getActionUrlAttribute(): ?string
    {
        $data = $this->formatted_data;
        return $data['action_url'] ?? null;
    }

    /**
     * دسته‌بندی اعلان
     */
    public function getCategoryAttribute(): string
    {
        $data = $this->formatted_data;
        if (!empty($data['category'])) {
            return $data['category'];
        }

        // تشخیص خودکار دسته‌بندی برای اعلان‌های قدیمی یا متفرقه
        if (str_contains($this->type, 'Snooze') || str_contains($this->type, 'Reminder')) {
            return 'reminders';
        }
        if (str_contains($this->type, 'Workflow')) {
            return 'workflows';
        }
        if (str_contains($this->type, 'Task')) {
            return 'tasks';
        }

        return 'system';
    }

    /**
     * اولویت اعلان (low, normal, high, urgent)
     */
    public function getPriorityAttribute(): string
    {
        $data = $this->formatted_data;
        return $data['priority'] ?? 'normal';
    }

    /**
     * سطح اهمیت (info, success, warning, danger)
     */
    public function getSeverityAttribute(): string
    {
        $data = $this->formatted_data;
        if (!empty($data['severity'])) {
            return $data['severity'];
        }

        if (str_contains($this->type, 'Escalation') || ($this->priority === 'urgent')) {
            return 'danger';
        }

        return 'info';
    }

    /**
     * نام فارسی دسته‌بندی
     */
    public function getCategoryLabelAttribute(): string
    {
        $categories = config('notifications.categories', []);
        return $categories[$this->category]['label'] ?? 'سیستمی';
    }

    /**
     * اسکوپ فیلتر اعلان‌های خوانده نشده
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * اسکوپ فیلتر اعلان‌های خوانده شده
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * اسکوپ فیلتر بر اساس دسته‌بندی
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        if ($category === 'all' || empty($category)) {
            return $query;
        }

        return $query->where(function ($q) use ($category) {
            $q->where('data->category', $category);
            
            // سازگاری با رکوردهای قدیمی که فیلد category در JSON نداشتند
            if ($category === 'reminders') {
                $q->orWhere('type', 'like', '%Reminder%')
                  ->orWhere('type', 'like', '%Snooze%');
            } elseif ($category === 'workflows') {
                $q->orWhere('type', 'like', '%Workflow%');
            } elseif ($category === 'tasks') {
                $q->orWhere('type', 'like', '%Task%');
            } elseif ($category === 'system') {
                $q->orWhere(function ($sq) {
                    $sq->whereNull('data->category')
                       ->where('type', 'not like', '%Reminder%')
                       ->where('type', 'not like', '%Snooze%')
                       ->where('type', 'not like', '%Workflow%')
                       ->where('type', 'not like', '%Task%');
                });
            }
        });
    }

    /**
     * اسکوپ فیلتر بر اساس اولویت
     */
    public function scopePriority(Builder $query, string $priority): Builder
    {
        if ($priority === 'all' || empty($priority)) {
            return $query;
        }

        return $query->where('data->priority', $priority);
    }

    /**
     * اسکوپ جستجوی متنی در عنوان و پیام
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('data->title', 'like', "%{$term}%")
              ->orWhere('data->message', 'like', "%{$term}%")
              ->orWhere('data->description', 'like', "%{$term}%");
        });
    }
}
