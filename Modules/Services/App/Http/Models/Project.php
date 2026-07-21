<?php

namespace Modules\Services\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Clients\Entities\Client;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'services_projects';

    protected $fillable = [
        'name',
        'code',
        'service_id',
        'customer_id',
        'assigned_user_id',
        'status_id',
        'description',
        'start_date',
        'end_date',
        'budget',
        'priority',
        'progress',
        'meta',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'integer',
        'progress' => 'integer',
        'meta' => 'array',
    ];


    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'project_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest();
    }


    public function isReadonly(): bool
    {
        return $this->status?->is_readonly ?? false;
    }


    /**
     * Returns all project statuses as id => name array.
     */
    public static function statuses(): array
    {
        return Status::query()->where('type', 'project')->orderBy('sort_order')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Returns a Tailwind color string for a given status id.
     */
    public static function statusColor(int|string|null $statusId): string
    {
        if (!$statusId) return 'gray';

        $status = Status::find($statusId);
        if (!$status) return 'gray';

        // Map hex colors to Tailwind color names for badge classes
        $colorMap = [
            '#6366f1' => 'indigo',
            '#10b981' => 'emerald',
            '#f59e0b' => 'amber',
            '#ef4444' => 'red',
            '#3b82f6' => 'blue',
            '#8b5cf6' => 'purple',
            '#ec4899' => 'pink',
            '#14b8a6' => 'teal',
            '#f97316' => 'orange',
        ];

        return $colorMap[strtolower($status->color)] ?? 'gray';
    }

    /**
     * Returns the display label for a given status id.
     */
    public static function statusLabel(int|string|null $statusId): string
    {
        if (!$statusId) return '—';
        return Status::find($statusId)?->name ?? '—';
    }
}
