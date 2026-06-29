<?php

namespace Modules\Booking\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;
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

    public function getContractEntityType(): string
    {
        return 'treatment_plan';
    }

    public function getContractClientId(): ?int
    {
        return $this->client_id;
    }

    public function getContractTitle(): string
    {
        return 'قرارداد طرح درمان #' . $this->id . ' - ' . $this->client_name;
    }

    public function getContractTokens(): array
    {
        $tokens = [
            'patient_name' => $this->client_name,
            'plan_id' => (string) $this->id,
            'plan_status' => $this->status_label,
            'plan_total' => number_format((float) $this->total) . ' ' . ($this->currency ?: 'تومان'),
            'plan_final_payable' => number_format((float) $this->final_payable) . ' ' . ($this->currency ?: 'تومان'),
            'plan_discount' => number_format((float) $this->discount_value) . ' ' . ($this->currency ?: 'تومان'),
            'plan_tax' => number_format((float) $this->tax_value) . ' ' . ($this->currency ?: 'تومان'),
            'plan_notes' => $this->notes ?: '-',
            'today_jalali' => \Morilog\Jalali\Jalalian::now()->format('Y/m/d'),
            
            // Installment properties
            'installment_option_title' => $this->installment_option_title ?: 'نقدی',
            'installment_down_payment' => number_format((float) $this->installment_down_payment) . ' ' . ($this->currency ?: 'تومان'),
            'installment_monthly_amount' => number_format((float) $this->installment_monthly_amount) . ' ' . ($this->currency ?: 'تومان'),
            'installment_months' => (string) $this->installment_months,
            'installment_due_day' => (string) $this->installment_due_day,
            'installment_start_date' => $this->installment_start_date ?: '-',
        ];

        // Items table
        $itemsHtml = '<table class="w-full border-collapse border border-gray-300 text-sm text-right" style="width: 100%; border-collapse: collapse; text-align: right; font-family: inherit; margin: 15px 0;">';
        $itemsHtml .= '<thead><tr class="bg-gray-100"><th class="border p-2">عنوان خدمت</th><th class="border p-2">قیمت واحد</th><th class="border p-2">تعداد</th><th class="border p-2">تخفیف</th><th class="border p-2">قیمت کل</th></tr></thead>';
        $itemsHtml .= '<tbody>';
        if (is_array($this->items)) {
            foreach ($this->items as $item) {
                $price = number_format((float) ($item['price'] ?? 0));
                $qty = $item['quantity'] ?? 1;
                $discount = number_format((float) ($item['discount'] ?? 0));
                $total = number_format((float) ($item['total'] ?? 0));
                $title = $item['title'] ?? ($item['service_name'] ?? '-');
                $itemsHtml .= "<tr><td class='border p-2'>{$title}</td><td class='border p-2'>{$price}</td><td class='border p-2'>{$qty}</td><td class='border p-2'>{$discount}</td><td class='border p-2'>{$total}</td></tr>";
            }
        } else {
            $itemsHtml .= "<tr><td colspan='5' class='border p-2 text-center'>هیچ آیتمی وجود ندارد</td></tr>";
        }
        $itemsHtml .= '</tbody></table>';
        $tokens['plan_items_table'] = $itemsHtml;

        // Installment breakdown table
        $breakdownHtml = '<table class="w-full border-collapse border border-gray-300 text-sm text-right" style="width: 100%; border-collapse: collapse; text-align: right; font-family: inherit; margin: 15px 0;">';
        $breakdownHtml .= '<thead><tr class="bg-gray-100"><th class="border p-2">شماره قسط</th><th class="border p-2">مبلغ قسط</th><th class="border p-2">تاریخ سررسید</th><th class="border p-2">وضعیت پرداخت</th></tr></thead>';
        $breakdownHtml .= '<tbody>';
        if (is_array($this->installment_breakdown)) {
            foreach ($this->installment_breakdown as $index => $inst) {
                $num = $index + 1;
                $amount = number_format((float) ($inst['amount'] ?? 0)) . ' ' . ($this->currency ?: 'تومان');
                $dueDate = $inst['due_date'] ?? '-';
                $status = ($inst['is_paid'] ?? false) ? 'پرداخت شده' : 'معوق/در انتظار';
                $breakdownHtml .= "<tr><td class='border p-2'>قسط {$num}</td><td class='border p-2'>{$amount}</td><td class='border p-2'>{$dueDate}</td><td class='border p-2'>{$status}</td></tr>";
            }
        } else {
            $breakdownHtml .= "<tr><td colspan='4' class='border p-2 text-center'>جزئیات اقساط یافت نشد</td></tr>";
        }
        $breakdownHtml .= '</tbody></table>';
        $tokens['installment_breakdown_table'] = $breakdownHtml;

        // Cheques table
        $chequesHtml = '<table class="w-full border-collapse border border-gray-300 text-sm text-right" style="width: 100%; border-collapse: collapse; text-align: right; font-family: inherit; margin: 15px 0;">';
        $chequesHtml .= '<thead><tr class="bg-gray-100"><th class="border p-2">شماره چک</th><th class="border p-2">مبلغ چک</th><th class="border p-2">تاریخ سررسید</th><th class="border p-2">بانک صادرکننده</th><th class="border p-2">وضعیت</th></tr></thead>';
        $chequesHtml .= '<tbody>';
        if (is_array($this->generated_cheques)) {
            foreach ($this->generated_cheques as $cheque) {
                $chNum = $cheque['cheque_number'] ?? '-';
                $amount = number_format((float) ($cheque['amount'] ?? 0)) . ' ' . ($this->currency ?: 'تومان');
                $dueDate = $cheque['due_date'] ?? '-';
                $bank = $cheque['bank_name'] ?? '-';
                $status = ($cheque['is_passed'] ?? false) ? 'پاس شده' : 'در جریان';
                $chequesHtml .= "<tr><td class='border p-2'>{$chNum}</td><td class='border p-2'>{$amount}</td><td class='border p-2'>{$dueDate}</td><td class='border p-2'>{$bank}</td><td class='border p-2'>{$status}</td></tr>";
            }
        } else {
            $chequesHtml .= "<tr><td colspan='5' class='border p-2 text-center'>چکی ثبت نشده است</td></tr>";
        }
        $chequesHtml .= '</tbody></table>';
        $tokens['cheques_table'] = $chequesHtml;

        return $tokens;
    }
}
