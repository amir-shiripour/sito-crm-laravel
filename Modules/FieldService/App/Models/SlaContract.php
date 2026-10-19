<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaContract extends Model
{
    use HasFactory;

    protected $table = 'field_service_sla_contracts';

    protected $fillable = [
        'building_id',
        'contract_number',
        'title',
        'tier',
        'start_date',
        'end_date',
        'monthly_fee',
        'emergency_response_hours',
        'periodic_visits_per_month',
        'status',
        'terms',
    ];

    protected $casts = [
        'start_date'                => 'date',
        'end_date'                  => 'date',
        'monthly_fee'               => 'float',
        'emergency_response_hours'  => 'integer',
        'periodic_visits_per_month' => 'integer',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            'gold_sla' => 'قرارداد طلایی (پاسخ اضطراری ۲ ساعته)',
            'silver'   => 'قرارداد نقره‌ای (پاسخ اضطراری ۴ ساعته)',
            'bronze'   => 'قرارداد برنزی (پاسخ اضطراری ۱۲ ساعته)',
            default    => 'قرارداد استاندارد',
        };
    }

    public function getTierBadgeClassAttribute(): string
    {
        return match ($this->tier) {
            'gold_sla' => 'bg-amber-100 text-amber-900 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-300 dark:border-amber-500/40',
            'silver'   => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600',
            'bronze'   => 'bg-orange-100 text-orange-900 dark:bg-orange-500/20 dark:text-orange-300 border border-orange-300 dark:border-orange-500/40',
            default    => 'bg-indigo-100 text-indigo-900 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-500/40',
        };
    }
}
