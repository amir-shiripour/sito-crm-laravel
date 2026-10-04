<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PropertyRevision extends Model
{
    protected $table = 'property_revisions';

    protected $fillable = [
        'property_id',
        'user_id',
        'reviewer_id',
        'type',
        'status',
        'old_data',
        'new_data',
        'changes_summary',
        'notes',
        'reviewed_at',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'changes_summary' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'initial_creation' => 'ثبت اولیه اقامتگاه',
            'property_update' => 'ویرایش اطلاعات اصلی اقامتگاه',
            'rental_config_update' => 'ویرایش نرخ‌ها و شرایط اقامتگاه',
            default => 'ویرایش اقامتگاه',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'در انتظار بررسی',
            'approved' => 'تأیید شده',
            'rejected' => 'رد شده',
            default => $this->status,
        };
    }
}
