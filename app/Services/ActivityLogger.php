<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * ثبت لاگ فعالیت جدید.
     *
     * @param string $type نوع فعالیت (مثلاً 'snooze_reminder')
     * @param string $description توضیحات فعالیت به زبان فارسی
     * @param Model|null $subject مدل مرتبط
     * @param array $properties اطلاعات اضافی و متغیرها
     * @return ActivityLog
     */
    public static function log(string $type, string $description, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id'       => Auth::id(),
            'activity_type' => $type,
            'description'   => $description,
            'subject_type'  => $subject ? get_class($subject) : null,
            'subject_id'    => $subject ? $subject->getKey() : null,
            'properties'    => $properties,
            'ip_address'    => Request::ip(),
            'user_agent'    => Request::userAgent(),
        ]);
    }
}
