<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectActivity extends Model
{
    public $timestamps = false;

    protected $table = 'projects_activities';

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'action',
        'subject',
        'old_value',
        'new_value',
        'created_at',
    ];

    protected $casts = [
        'old_value'  => 'array',
        'new_value'  => 'array',
        'created_at' => 'datetime',
    ];


    private const ACTION_MAP = [
        'project.created' => ['label' => 'پروژه ایجاد شد', 'color' => 'emerald'],
        'project.updated' => ['label' => 'پروژه ویرایش شد', 'color' => 'blue'],
        'project.status_changed' => ['label' => 'وضعیت پروژه تغییر کرد', 'color' => 'violet'],
        'project.canceled' => ['label' => 'پروژه لغو شد', 'color' => 'rose'],
        'task.created' => ['label' => 'کار جدید اضافه شد', 'color' => 'indigo'],
        'task.updated' => ['label' => 'کار ویرایش شد', 'color' => 'sky'],
        'task.status_changed' => ['label' => 'وضعیت کار تغییر کرد', 'color' => 'amber'],
        'task.completed' => ['label' => 'کار تکمیل شد', 'color' => 'emerald'],
        'task.deleted' => ['label' => 'کار حذف شد', 'color' => 'red'],
        'member.added' => ['label' => 'عضو جدید اضافه شد', 'color' => 'teal'],
        'member.removed' => ['label' => 'عضو حذف شد', 'color' => 'orange'],
        'document.uploaded' => ['label' => 'سند بارگذاری شد', 'color' => 'cyan'],
        'document.deleted' => ['label' => 'سند حذف شد', 'color' => 'red'],
        'checklist.toggled' => ['label' => 'آیتم چک‌لیست تغییر کرد', 'color' => 'lime'],
        'message.sent'    => ['label' => 'پیام ارسال شد',           'color' => 'violet'],
        'message.deleted' => ['label' => 'پیام حذف شد',             'color' => 'red'],
        'message.pinned'  => ['label' => 'پیام پین شد',             'color' => 'amber'],
        'message.unpinned'=> ['label' => 'پیام از پین خارج شد',     'color' => 'gray'],
    ];

    public function actionLabel(): string
    {
        return self::ACTION_MAP[$this->action]['label'] ?? $this->action;
    }

    public function actionColor(): string
    {
        return self::ACTION_MAP[$this->action]['color'] ?? 'gray';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log(
        int    $projectId,
        string $action,
        string $subject = '',
        ?int   $taskId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?int   $userId = null
    ): self
    {
        return static::create([
            'project_id' => $projectId,
            'task_id' => $taskId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'subject' => $subject,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'created_at' => now(),
        ]);
    }
}
