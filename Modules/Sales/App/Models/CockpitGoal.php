<?php

namespace Modules\Sales\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ClientCalls\Entities\ClientCall;
use Modules\Clients\Entities\Client;
use Modules\Tasks\Entities\Task;

class CockpitGoal extends Model
{
    protected $table = 'cockpit_goals';

    protected $fillable = [
        'user_id',
        'goal_type',
        'target_value',
        'period',
        'active_from',
        'active_until',
        'is_active',
        'note',
        'created_by'
    ];

    protected $casts = [
        'active_from' => 'date',
        'active_until' => 'date',
        'is_active' => 'boolean',
        'target_value' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('active_from')
                    ->orWhere('active_from', '<=', today());
            })
            ->where(function ($q) {
                $q->whereNull('active_until')
                    ->orWhere('active_until', '>=', today());
            });
    }

    public static function getActiveGoalForUser(User $user, ?string $type = null): ?self
    {
        $query = self::where('user_id', $user->id)->active();

        if ($type) {
            $query->where('goal_type', $type);
        }

        return $query->first();
    }

    public function calculateProgress(User $user): array
    {
        $current = 0;

        switch ($this->goal_type) {
            case 'daily_calls':
                if (class_exists(ClientCall::class)) {
                    $current = ClientCall::where('user_id', $user->id)
                        ->today()
                        ->count();
                }
                break;

            case 'daily_answered':
                if (class_exists(ClientCall::class)) {
                    $current = ClientCall::where('user_id', $user->id)
                        ->today()
                        ->answered()
                        ->count();
                }
                break;

            case 'weekly_followups':
                if (class_exists(Task::class)) {
                    $current = Task::where('assignee_id', $user->id)
                        ->where('task_type', Task::TYPE_FOLLOW_UP)
                        ->where('status', Task::STATUS_DONE)
                        ->whereBetween('completed_at', [
                            now()->startOfWeek(),
                            now()->endOfWeek(),
                        ])
                        ->count();
                }
                break;

            case 'monthly_clients':
                if (class_exists(Client::class)) {
                    $current = Client::where('created_by', $user->id)
                        ->whereBetween('created_at', [
                            now()->startOfMonth(),
                            now()->endOfMonth(),
                        ])
                        ->count();
                }
                break;

            case 'conversion_rate':
                if (class_exists(Client::class)) {
                    $total = Client::where('created_by', $user->id)->count();
                    $converted = Client::where('created_by', $user->id)
                        ->whereHas('orders')
                        ->count();
                    $current = $total > 0 ? (int) (($converted / $total) * 100) : 0;
                }
                break;

            case 'talk_time_minutes':
                if (class_exists(ClientCall::class)) {
                    $seconds = ClientCall::where('user_id', $user->id)
                        ->today()
                        ->sum('duration_seconds');
                    $current = (int) ($seconds / 60);
                }
                break;
        }

        $target = $this->target_value;
        $percent = $target > 0 ? min(100, (int) (($current / $target) * 100)) : 0;

        return [
            'has_goal' => true,
            'goal_id' => $this->id,
            'type' => $this->goal_type,
            'label' => $this->getGoalTypeLabel(),
            'current' => $current,
            'target' => $target,
            'percent' => $percent,
            'period' => $this->period,
        ];
    }

    public function getGoalTypeLabel(): string
    {
        return self::goalTypeLabels()[$this->goal_type] ?? $this->goal_type;
    }

    public static function goalTypeLabels(): array
    {
        return [
            'daily_calls' => 'تعداد تماس‌های امروز',
            'daily_answered' => 'تعداد تماس‌های موفق امروز',
            'weekly_followups' => 'پیگیری‌های موفق این هفته',
            'monthly_clients' => 'مشتریان جدید این ماه',
            'conversion_rate' => 'نرخ تبدیل مشتریان (درصد)',
            'talk_time_minutes' => 'مدت زمان مکالمه امروز (دقیقه)',
        ];
    }
}
