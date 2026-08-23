<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectStatus extends Model
{
    use SoftDeletes;

    protected $table = 'projects_statuses';

    protected $fillable = [
        'name',
        'color',
        'icon',
        'type',
        'is_final',
        'is_default',
        'is_readonly',
        'attributes',
        'allowed_transitions',
        'allowed_roles',
        'allowed_users',
        'sort_order',
    ];

    protected $casts = [
        'attributes' => 'array',
        'allowed_transitions' => 'array',
        'allowed_roles' => 'array',
        'allowed_users' => 'array',
        'is_final' => 'boolean',
        'is_default' => 'boolean',
        'is_readonly' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type)->orderBy('sort_order');
    }

    public static function defaultFor(string $type): ?self
    {
        return self::where('type', $type)->where('is_default', true)->first();
    }

    public static function canceledFor(string $type = 'project'): ?self
    {
        $statuses = self::where('type', $type)->get();

        $status = $statuses->first(fn($s) => $s->isCanceled());
        if ($status) {
            return $status;
        }

        return $statuses->first(fn($s) => str_contains($s->name, 'لغو') || str_contains(strtolower($s->name), 'cancel'));
    }

    public static function queuedFor(string $type = 'task'): ?self
    {
        $statuses = self::where('type', $type)->get();
        $queued = $statuses->first(fn($s) => $s->isQueued() && !$s->isCompleted() && !$s->isCanceled())
            ?? $statuses->first(fn($s) => (str_contains($s->name, 'صف') || str_contains(strtolower($s->name), 'queue')) && !$s->isCompleted() && !$s->isCanceled());

        if ($queued) {
            return $queued;
        }

        $default = self::defaultFor($type);
        if ($default && !$default->isCompleted() && !$default->isCanceled()) {
            return $default;
        }

        return null;
    }

    public static function inProgressFor(string $type = 'task'): ?self
    {
        $statuses = self::where('type', $type)->get();
        return $statuses->first(fn($s) => $s->isInProgress() && !$s->isCompleted() && !$s->isCanceled())
            ?? $statuses->first(fn($s) => (str_contains($s->name, 'انجام') || str_contains(strtolower($s->name), 'progress')) && !$s->isCompleted() && !$s->isCanceled());
    }

    public static function completedFor(string $type = 'task'): ?self
    {
        $statuses = self::where('type', $type)->get();
        return $statuses->first(fn($s) => $s->isCompleted() && !$s->isCanceled())
            ?? $statuses->first(fn($s) => (str_contains($s->name, 'تکمیل') || str_contains(strtolower($s->name), 'complete')) && !$s->isCanceled());
    }

    public static function delayedFor(string $type = 'task'): ?self
    {
        $statuses = self::where('type', $type)->get();
        return $statuses->first(fn($s) => $s->isDelayed() && !$s->isCompleted() && !$s->isCanceled())
            ?? $statuses->first(fn($s) => (str_contains($s->name, 'تعویق') || str_contains(strtolower($s->name), 'delay')) && !$s->isCompleted() && !$s->isCanceled());
    }

    public function attr(string $key, bool $default = false): bool
    {
        $arr = $this->getAttributeValue('attributes') ?? [];
        if (is_string($arr)) {
            $arr = json_decode($arr, true) ?? [];
        }
        return (bool) ($arr[$key] ?? $default);
    }

    public function isDelayed(): bool
    {
        if ($this->isCanceled() || $this->isCompleted()) {
            return false;
        }

        return $this->attr('is_delayed')
            || str_contains($this->name, 'تعویق')
            || str_contains(strtolower($this->name), 'delay');
    }

    public function isCanceled(): bool
    {
        return $this->attr('is_canceled')
            || str_contains($this->name, 'لغو')
            || str_contains(strtolower($this->name), 'cancel');
    }

    public function isCompleted(): bool
    {
        if ($this->isCanceled()) {
            return false;
        }

        return $this->attr('is_completed')
            || str_contains($this->name, 'تکمیل')
            || str_contains(strtolower($this->name), 'complete')
            || ((bool)$this->is_final && !$this->isDelayed() && !$this->isQueued() && !$this->isInProgress());
    }

    public function isInProgress(): bool
    {
        if ($this->isCanceled() || $this->isCompleted()) {
            return false;
        }

        return $this->attr('is_in_progress')
            || str_contains($this->name, 'انجام')
            || str_contains(strtolower($this->name), 'progress');
    }

    public function isQueued(): bool
    {
        if ($this->isCanceled() || $this->isCompleted() || $this->isDelayed() || $this->isInProgress()) {
            return false;
        }

        return $this->attr('is_queued')
            || (bool)$this->is_default
            || str_contains($this->name, 'صف')
            || str_contains(strtolower($this->name), 'queue');
    }

    public function canBeAppliedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'superadmin'])) {
            return true;
        }

        $roles = $this->allowed_roles;
        $users = $this->allowed_users;

        if (empty($roles) && empty($users)) {
            return true;
        }

        if (!empty($users) && in_array($user->id, $users)) {
            return true;
        }

        if (!empty($roles) && $user->hasAnyRole($roles)) {
            return true;
        }

        return false;
    }
}
