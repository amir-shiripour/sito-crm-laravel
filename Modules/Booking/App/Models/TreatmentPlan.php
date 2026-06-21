<?php

namespace Modules\Booking\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;
use App\Models\User;

class TreatmentPlan extends Model
{
    use HasFactory;

    protected $table = 'treatment_plans';

    protected $fillable = [
        'user_id',
        'client_id',
        'patient_id',
        'patient_name',
        'status',
        'notes',
        'discount_amount',
        'discount_type',
        'subtotal',
        'discount_value',
        'total',
        'currency',
        'items',
        'installment_option_id',
        'installment_option_title',
        'installment_down_payment',
        'installment_monthly_amount',
        'installment_fee_value',
        'installment_months',
        'installment_count',
    ];

    protected $casts = [
        'items'           => 'array',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_value'  => 'decimal:2',
        'total'           => 'decimal:2',
        'client_id'       => 'integer',
        'patient_id'      => 'integer',
        'user_id'         => 'integer',
        'installment_down_payment' => 'decimal:2',
        'installment_monthly_amount' => 'decimal:2',
        'installment_fee_value' => 'decimal:2',
        'installment_months' => 'integer',
        'installment_count' => 'integer',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // alias so controller can use ->creator
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeDrafts($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'draft'     => 'پیش‌نویس',
            'confirmed' => 'تأیید شده',
            default     => ucfirst($this->status ?? ''),
        };
    }

    public function getClientNameAttribute()
    {
        return $this->client?->full_name
            ?? $this->patient_name
            ?? 'بدون مشتری';
    }
}
