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
        'tax_value',
        'total',
        'final_payable',
        'currency',
        'items',

        // Installment Base Fields
        'installment_option_id',
        'installment_option_title',
        'installment_down_payment',
        'installment_monthly_amount',
        'installment_fee_value',
        'installment_months',
        'installment_count',

        // Installment Detailed Fields
        'installment_due_day',
        'installment_start_date',
        'installment_interval_months',
        'installment_down_payment_percent',
        'installment_fee_percent',
        'installment_cash_now',
        'installment_uncovered_total',
        'installment_breakdown',
        'generated_cheques',
        'assigned_users',
    ];

    protected $casts = [
        'items' => 'array',
        'installment_breakdown' => 'array',
        'generated_cheques' => 'array',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'tax_value' => 'decimal:2',
        'total' => 'decimal:2',
        'final_payable' => 'decimal:2',
        'client_id' => 'integer',
        'patient_id' => 'integer',
        'user_id' => 'integer',
        'installment_down_payment' => 'decimal:2',
        'installment_monthly_amount' => 'decimal:2',
        'installment_fee_value' => 'decimal:2',
        'installment_months' => 'integer',
        'installment_count' => 'integer',
        'installment_due_day' => 'integer',
        'installment_interval_months' => 'integer',
        'installment_down_payment_percent' => 'decimal:2',
        'installment_fee_percent' => 'decimal:2',
        'installment_cash_now' => 'decimal:2',
        'installment_uncovered_total' => 'decimal:2',
        'assigned_users' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (TreatmentPlan $plan) {
            if (is_array($plan->items)) {
                $items = $plan->items;
                $updated = false;
                foreach ($items as &$item) {
                    if (empty($item['item_uuid'])) {
                        $item['item_uuid'] = (string) \Illuminate\Support\Str::uuid();
                        $updated = true;
                    }
                }
                if ($updated) {
                    $plan->items = $items;
                }
            }
        });
    }

    public function workflowBindings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Booking\App\Models\TreatmentPlanWorkflowBinding::class, 'treatment_plan_id');
    }

    public function workflowInstances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Workflows\Entities\WorkflowInstance::class, 'related_id')
            ->where('related_type', 'TREATMENT_PLAN');
    }

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

    public function snapshots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TreatmentPlanSnapshot::class, 'treatment_plan_id')->orderBy('created_at', 'desc');
    }

    public function getStatusLabelAttribute()
    {
        $setting = \Modules\Booking\Entities\BookingSetting::current();
        $statuses = $setting->cure_statuses ?? [];
        foreach ($statuses as $st) {
            if ($st['id'] === $this->status) {
                return $st['name'];
            }
        }
        return match ($this->status) {
            'draft'     => 'پیش‌نویس',
            'confirmed' => 'تأیید شده',
            default     => ucfirst($this->status ?? ''),
        };
    }

    public function getStatusColorAttribute()
    {
        $setting = \Modules\Booking\Entities\BookingSetting::current();
        $statuses = $setting->cure_statuses ?? [];
        foreach ($statuses as $st) {
            if ($st['id'] === $this->status) {
                return $st['color'];
            }
        }
        return '#6b7280'; // default gray
    }

    public function canTransitionTo(string $newStatus, User $user): bool
    {
        $setting = \Modules\Booking\Entities\BookingSetting::current();
        $statuses = $setting->cure_statuses ?? [];

        $currentStatusData = null;
        $newStatusData = null;
        foreach ($statuses as $st) {
            if ($st['id'] === $this->status) {
                $currentStatusData = $st;
            }
            if ($st['id'] === $newStatus) {
                $newStatusData = $st;
            }
        }

        // If the new status is not defined in settings, don't allow
        if (!$newStatusData) {
            return false;
        }

        // If it's the same status, allow it (e.g. updating notes or items without changing status)
        if ($this->status === $newStatus) {
            return true;
        }

        // Check if transition is allowed from current status
        $allowedFrom = $newStatusData['allowed_from'] ?? [];
        if (!empty($allowedFrom) && !in_array($this->status, $allowedFrom)) {
            return false;
        }

        // Check allowed roles for the new status. If empty, anyone is allowed.
        $allowedRoles = $newStatusData['allowed_roles'] ?? [];
        if (!empty($allowedRoles)) {
            $userRoleIds = $user->roles->pluck('id')->toArray();
            if (empty(array_intersect($userRoleIds, $allowedRoles))) {
                return false;
            }
        }

        return true;
    }

    public function getClientNameAttribute()
    {
        return $this->client?->full_name
            ?? $this->patient_name
            ?? 'بدون مشتری';
    }
}
