<?php

declare(strict_types=1);

namespace Modules\Sales\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

final class SalesDeal extends Model
{
    use SoftDeletes;

    protected $table = 'sales_deals';

    protected $fillable = [
        'title',
        'description',
        'client_id',
        'pipeline_stage_id',
        'user_id',
        'expected_revenue',
        'actual_revenue',
        'expected_close_date',
        'probability',
        'stage_entered_at',
        'lead_source',
        'loss_reason_id',
        'custom_fields',
        'status',
        'created_by',
    ];

    protected $casts = [
        'expected_revenue' => 'float',
        'actual_revenue' => 'float',
        'expected_close_date' => 'date',
        'stage_entered_at' => 'datetime',
        'probability' => 'integer',
        'custom_fields' => 'array',
    ];

    /**
     * رابطه با مشتری
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(\Modules\Clients\Entities\Client::class, 'client_id');
    }

    /**
     * رابطه با مرحله خط لوله (Stage)
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(SalesPipeline::class, 'pipeline_stage_id');
    }

    /**
     * کارشناس فروش (Account Manager)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ایجاد کننده پرونده
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * علت شکست پرونده
     */
    public function lossReason(): BelongsTo
    {
        return $this->belongsTo(SalesLossReason::class, 'loss_reason_id');
    }

    /**
     * تماس‌های مربوط به این پرونده
     */
    public function calls(): HasMany
    {
        return $this->hasMany(SalesCall::class, 'deal_id');
    }

    /**
     * وظایف/پیگیری‌های مربوط به این پرونده (Polymorphic-style link)
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(\Modules\Tasks\Entities\Task::class, 'related_id')
            ->where('related_type', 'DEAL');
    }

    /**
     * درآمد موزون (Weighted Revenue)
     * Expected Revenue * Probability / 100
     */
    public function getWeightedRevenueAttribute(): float
    {
        $probability = $this->probability ?? 0;
        return (float) ($this->expected_revenue * ($probability / 100));
    }

    /**
     * اسکوپ فیلترینگ بر اساس دسترسی کاربر
     */
    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('super-admin') || $user->can('sales.deals.view.all') || $user->can('sales.manage')) {
            return $query;
        }

        // فقط پرونده‌هایی که به خودش اختصاص داده شده یا خودش ساخته است
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('created_by', $user->id);
        });
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeWon(Builder $query): Builder
    {
        return $query->where('status', 'won');
    }

    public function scopeLost(Builder $query): Builder
    {
        return $query->where('status', 'lost');
    }
}
