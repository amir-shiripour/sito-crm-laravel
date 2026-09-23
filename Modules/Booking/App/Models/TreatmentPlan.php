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

        // Check allowed roles for the new status. If empty, anyone is allowed.
        $allowedRoles = $newStatusData['allowed_roles'] ?? [];
        if (!empty($allowedRoles)) {
            $userRoleIds = $user->roles->pluck('id')->toArray();
            if (empty(array_intersect($userRoleIds, $allowedRoles))) {
                return false;
            }
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
        $this->loadMissing('client');
        $client = $this->client;

        $sysCurrency = function_exists('get_setting') ? get_setting('payment_currency', 'toman') : 'toman';
        $factor = ($sysCurrency === 'toman') ? 10 : 1;
        $unitLabel = ($sysCurrency === 'toman') ? 'تومان' : 'ریال';

        $formatMoney = function($amount) use ($factor) {
            return number_format((float)$amount * $factor) . ' ریال';
        };

        $formatMoneyWords = function($amount) use ($factor, $sysCurrency, $unitLabel) {
            $val = (float) $amount;
            if ($val <= 0) {
                return 'صفر ' . $unitLabel;
            }
            $convertedNum = ($sysCurrency === 'toman') ? $val : ($val * $factor);
            if (class_exists(\Modules\Accounting\App\Services\NumberToWords::class)) {
                return \Modules\Accounting\App\Services\NumberToWords::convert((int)$convertedNum) . ' ' . $unitLabel;
            }
            return number_format($convertedNum) . ' ' . $unitLabel;
        };

        $totalVal = (float) $this->total;
        $finalPayableVal = (float) $this->final_payable;
        $downPaymentVal = (float) $this->installment_down_payment;
        $monthlyAmountVal = (float) $this->installment_monthly_amount;
        $discountVal = (float) $this->discount_value;
        $taxVal = (float) $this->tax_value;
        $remainingVal = max(0, $finalPayableVal - $downPaymentVal);

        // Down payment percentage
        $downPaymentPercentVal = $this->installment_down_payment_percent;
        if ($downPaymentPercentVal === null || $downPaymentPercentVal === '') {
            $downPaymentPercentVal = ($finalPayableVal > 0 && $downPaymentVal > 0)
                ? round(($downPaymentVal / $finalPayableVal) * 100, 2)
                : 0;
        }
        $formattedPercent = (float)$downPaymentPercentVal == (int)$downPaymentPercentVal 
            ? (string)(int)$downPaymentPercentVal 
            : (string)round((float)$downPaymentPercentVal, 2);

        // Installment profit / fee (سود و کارمزد اقساط)
        $feeVal = (float) $this->installment_fee_value;
        $feePercentVal = $this->installment_fee_percent;
        if ($feeVal <= 0 && $feePercentVal > 0 && $totalVal > 0) {
            $feeVal = ($totalVal * (float)$feePercentVal) / 100;
        }
        if (($feePercentVal === null || $feePercentVal === '') && $totalVal > 0 && $feeVal > 0) {
            $feePercentVal = round(($feeVal / $totalVal) * 100, 2);
        }
        $formattedFeePercent = (float)$feePercentVal == (int)$feePercentVal 
            ? (string)(int)$feePercentVal 
            : (string)round((float)$feePercentVal, 2);

        $tokens = [
            'patient_name' => $this->client_name ?: ($client?->full_name ?: '-'),
            'patient_phone' => $client?->phone ?: '-',
            'patient_national_code' => $client?->national_code ?: '-',
            'patient_case_number' => $client?->case_number ?: '-',
            'patient_email' => $client?->email ?: '-',
            'plan_id' => (string) $this->id,
            'plan_status' => $this->status_label,
            'plan_date' => $this->created_at ? \Morilog\Jalali\Jalalian::fromCarbon($this->created_at)->format('Y/m/d') : '-',
            'plan_notes' => $this->notes ?: '-',
            'today_jalali' => \Morilog\Jalali\Jalalian::now()->format('Y/m/d'),
            
            // Monetary Numbers
            'plan_total' => $formatMoney($totalVal),
            'plan_final_payable' => $formatMoney($finalPayableVal),
            'plan_discount' => $formatMoney($discountVal),
            'plan_tax' => $formatMoney($taxVal),
            'system_currency' => $unitLabel,

            // Monetary Amounts in Words
            'plan_total_in_words' => $formatMoneyWords($totalVal),
            'plan_final_payable_in_words' => $formatMoneyWords($finalPayableVal),
            'plan_discount_in_words' => $formatMoneyWords($discountVal),
            'plan_tax_in_words' => $formatMoneyWords($taxVal),

            // Clinic details
            'clinic_name' => function_exists('get_setting') ? (get_setting('business_name') ?: 'کلینیک') : 'کلینیک',
            'clinic_phone' => function_exists('get_setting') ? (get_setting('business_phone') ?: '-') : '-',
            'clinic_address' => function_exists('get_setting') ? (get_setting('business_address') ?: '-') : '-',
            'total_cheques' => (string) (is_array($this->generated_cheques) ? count($this->generated_cheques) : 0),
            'total_installment_stages' => (string) (is_array($this->generated_cheques) ? count($this->generated_cheques) : 0),
            
            // Installment properties
            'installment_option_title' => $this->installment_option_title ?: 'نقدی',
            'installment_down_payment' => $formatMoney($downPaymentVal),
            'installment_down_payment_in_words' => $formatMoneyWords($downPaymentVal),
            'installment_down_payment_percent' => $formattedPercent,
            'installment_down_payment_percent_label' => $formattedPercent . ' درصد',
            
            // Installment profit / fee (سود و کارمزد اقساط)
            'installment_fee_value' => $formatMoney($feeVal),
            'installment_fee_value_in_words' => $formatMoneyWords($feeVal),
            'installment_fee_percent' => $formattedFeePercent,
            'installment_fee_percent_label' => $formattedFeePercent . ' درصد',
            'installment_profit_amount' => $formatMoney($feeVal),
            'installment_profit_in_words' => $formatMoneyWords($feeVal),
            'installment_profit_percent' => $formattedFeePercent,
            'installment_profit_percent_label' => $formattedFeePercent . ' درصد',
            'installment_interest_amount' => $formatMoney($feeVal),
            'installment_interest_in_words' => $formatMoneyWords($feeVal),
            'installment_interest_percent' => $formattedFeePercent,
            'installment_interest_percent_label' => $formattedFeePercent . ' درصد',

            'installment_monthly_amount' => $formatMoney($monthlyAmountVal),
            'installment_monthly_amount_in_words' => $formatMoneyWords($monthlyAmountVal),
            'installment_remaining_amount' => $formatMoney($remainingVal),
            'installment_remaining_amount_in_words' => $formatMoneyWords($remainingVal),
            'installment_months' => (string) ($this->installment_months ?: 0),
            'installment_due_day' => (string) ($this->installment_due_day ?: 1),
            'installment_start_date' => $this->installment_start_date ?: '-',
        ];

        // 1. Items table (Services Table - exactly matching the sample PDF)
        $itemsHtml = '<table class="w-full border-collapse border border-gray-300 dark:border-gray-700 text-sm text-right text-gray-800 dark:text-gray-200" style="width: 100%; border-collapse: collapse; text-align: right; font-family: inherit; margin: 15px 0;">';
        $itemsHtml .= '<thead><tr class="bg-gray-100 dark:bg-gray-900/50" style="background-color: #f3f4f6;">';
        $itemsHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 6%;">ردیف</th>';
        $itemsHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-right" style="border: 1px solid #d1d5db; padding: 8px; text-align: right; width: 34%;">خدمات</th>';
        $itemsHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 16%;">برند</th>';
        $itemsHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 10%;">تعداد</th>';
        $itemsHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 17%;">قیمت هر واحد</th>';
        $itemsHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 17%;">قیمت کل</th>';
        $itemsHtml .= '</tr></thead>';
        $itemsHtml .= '<tbody>';

        if (is_array($this->items) && count($this->items) > 0) {
            $itemRowIndex = 1;
            foreach ($this->items as $item) {
                $itemPrice = (float) ($item['price'] ?? 0);
                $basePriceVal = (float) ($item['base_price'] ?? 0);
                $qty = (float) ($item['quantity'] ?? 1);
                if ($itemPrice == 0 && $basePriceVal > 0) {
                    $itemPrice = $basePriceVal / max(1, $qty);
                }
                $itemDiscountVal = (float) ($item['discount'] ?? 0);
                $itemTotal = (float) ($item['total'] ?? (($itemPrice * $qty) - $itemDiscountVal));

                $priceStr = $formatMoney($itemPrice);
                $totalStr = $formatMoney($itemTotal);
                $title = $item['title'] ?? ($item['service_name'] ?? '-');

                // Extract Brand Names
                $brandNames = [];
                if (!empty($item['brands']) && is_array($item['brands'])) {
                    foreach ($item['brands'] as $br) {
                        if (!empty($br['name'])) {
                            $brandNames[] = $br['name'];
                        } elseif (is_string($br)) {
                            $brandNames[] = $br;
                        }
                    }
                } elseif (!empty($item['brand'])) {
                    $brandNames[] = is_array($item['brand']) ? ($item['brand']['name'] ?? '') : $item['brand'];
                }
                $brandStr = !empty($brandNames) ? implode('، ', array_filter($brandNames)) : '-';

                $itemsHtml .= "<tr>";
                $itemsHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$itemRowIndex}</td>";
                $itemsHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2' style='border: 1px solid #d1d5db; padding: 8px;'>{$title}</td>";
                $itemsHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$brandStr}</td>";
                $itemsHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$qty}</td>";
                $itemsHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$priceStr}</td>";
                $itemsHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$totalStr}</td>";
                $itemsHtml .= "</tr>";
                $itemRowIndex++;
            }

            // Summary Footer Row (Matching PDF: مجموع هزینه: ... ریال (به حروف ...))
            $itemsHtml .= "</tbody>";
            $itemsHtml .= "<tfoot><tr class='bg-gray-50 dark:bg-gray-900/30 font-bold' style='background-color: #f9fafb; font-weight: bold;'>";
            $itemsHtml .= "<td colspan='4' class='border border-gray-300 dark:border-gray-700 p-2.5 text-right' style='border: 1px solid #d1d5db; padding: 10px; text-align: right;'>مجموع هزینه: {$tokens['plan_total']} (به حروف {$tokens['plan_total_in_words']})</td>";
            $itemsHtml .= "<td colspan='2' class='border border-gray-300 dark:border-gray-700 p-2.5 text-center' style='border: 1px solid #d1d5db; padding: 10px; text-align: center;'>{$tokens['plan_final_payable']}</td>";
            $itemsHtml .= "</tr></tfoot>";
        } else {
            $itemsHtml .= "<tr><td colspan='6' class='border border-gray-300 dark:border-gray-700 p-3 text-center' style='border: 1px solid #d1d5db; padding: 12px; text-align: center;'>هیچ آیتمی وجود ندارد</td></tr></tbody>";
        }

        $itemsHtml .= '</table>';
        $tokens['plan_items_table'] = $itemsHtml;

        // 2. Cheques Table (Matching the sample PDF columns and order: ردیف | شماره صیاد | به نام بانک | مبلغ | تاریخ سررسید)
        $chequesHtml = '<table class="w-full border-collapse border border-gray-300 dark:border-gray-700 text-sm text-right text-gray-800 dark:text-gray-200" style="width: 100%; border-collapse: collapse; text-align: right; font-family: inherit; margin: 15px 0;">';
        $chequesHtml .= '<thead><tr class="bg-gray-100 dark:bg-gray-900/50" style="background-color: #f3f4f6;">';
        $chequesHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 8%;">ردیف</th>';
        $chequesHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 26%;">شماره صیاد / شماره چک</th>';
        $chequesHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 24%;">به نام بانک</th>';
        $chequesHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 22%;">مبلغ</th>';
        $chequesHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 20%;">تاریخ سررسید</th>';
        $chequesHtml .= '</tr></thead>';
        $chequesHtml .= '<tbody>';

        if (is_array($this->generated_cheques) && count($this->generated_cheques) > 0) {
            $chIndex = 1;
            $sumCheques = 0;
            foreach ($this->generated_cheques as $cheque) {
                $chNum = !empty($cheque['sayad_number']) ? $cheque['sayad_number'] : (!empty($cheque['chequeNumber']) ? $cheque['chequeNumber'] : (!empty($cheque['cheque_number']) ? $cheque['cheque_number'] : '-'));
                $amountVal = (float) ($cheque['amount'] ?? 0);
                $sumCheques += $amountVal;
                $amountStr = $formatMoney($amountVal);
                $dueDate = $cheque['date'] ?? ($cheque['due_date'] ?? ($cheque['display_date'] ?? '-'));
                $bank = !empty($cheque['bankName']) ? $cheque['bankName'] : (!empty($cheque['bank_name']) ? $cheque['bank_name'] : '-');

                $chequesHtml .= "<tr>";
                $chequesHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$chIndex}</td>";
                $chequesHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$chNum}</td>";
                $chequesHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$bank}</td>";
                $chequesHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$amountStr}</td>";
                $chequesHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$dueDate}</td>";
                $chequesHtml .= "</tr>";
                $chIndex++;
            }

            $chequesHtml .= "</tbody>";
            $chequesHtml .= "<tfoot><tr class='bg-gray-50 dark:bg-gray-900/30 font-bold' style='background-color: #f9fafb; font-weight: bold;'>";
            $chequesHtml .= "<td colspan='3' class='border border-gray-300 dark:border-gray-700 p-2.5 text-right' style='border: 1px solid #d1d5db; padding: 10px; text-align: right;'>مجموع مبالغ چک‌ها (" . count($this->generated_cheques) . " فقره چک)</td>";
            $chequesHtml .= "<td colspan='2' class='border border-gray-300 dark:border-gray-700 p-2.5 text-center' style='border: 1px solid #d1d5db; padding: 10px; text-align: center;'>" . $formatMoney($sumCheques) . "</td>";
            $chequesHtml .= "</tr></tfoot>";
        } else {
            $chequesHtml .= "<tr><td colspan='5' class='border border-gray-300 dark:border-gray-700 p-3 text-center' style='border: 1px solid #d1d5db; padding: 12px; text-align: center;'>چکی ثبت نشده است</td></tr></tbody>";
        }

        $chequesHtml .= '</table>';
        $tokens['cheques_table'] = $chequesHtml;

        // 3. Installment Breakdown Table
        $breakdownHtml = '<table class="w-full border-collapse border border-gray-300 dark:border-gray-700 text-sm text-right text-gray-800 dark:text-gray-200" style="width: 100%; border-collapse: collapse; text-align: right; font-family: inherit; margin: 15px 0;">';
        $breakdownHtml .= '<thead><tr class="bg-gray-100 dark:bg-gray-900/50" style="background-color: #f3f4f6;">';
        $breakdownHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 10%;">ردیف</th>';
        $breakdownHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-right" style="border: 1px solid #d1d5db; padding: 8px; text-align: right; width: 35%;">عنوان قسط</th>';
        $breakdownHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 30%;">مبلغ قسط</th>';
        $breakdownHtml .= '<th class="border border-gray-300 dark:border-gray-700 p-2 text-center" style="border: 1px solid #d1d5db; padding: 8px; text-align: center; width: 25%;">تاریخ سررسید</th>';
        $breakdownHtml .= '</tr></thead>';
        $breakdownHtml .= '<tbody>';

        $installmentList = is_array($this->installment_breakdown) && count($this->installment_breakdown) > 0
            ? $this->installment_breakdown
            : (is_array($this->generated_cheques) ? $this->generated_cheques : []);

        if (!empty($installmentList)) {
            $instIndex = 1;
            $sumInstallments = 0;
            foreach ($installmentList as $inst) {
                $amountVal = (float) ($inst['amount'] ?? 0);
                $sumInstallments += $amountVal;
                $amountStr = $formatMoney($amountVal);
                $dueDate = $inst['date'] ?? ($inst['due_date'] ?? ($inst['display_date'] ?? '-'));
                $title = $inst['title'] ?? "قسط شماره {$instIndex}";

                $breakdownHtml .= "<tr>";
                $breakdownHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$instIndex}</td>";
                $breakdownHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2' style='border: 1px solid #d1d5db; padding: 8px;'>{$title}</td>";
                $breakdownHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$amountStr}</td>";
                $breakdownHtml .= "<td class='border border-gray-300 dark:border-gray-700 p-2 text-center' style='border: 1px solid #d1d5db; padding: 8px; text-align: center;'>{$dueDate}</td>";
                $breakdownHtml .= "</tr>";
                $instIndex++;
            }

            $breakdownHtml .= "</tbody>";
            $breakdownHtml .= "<tfoot><tr class='bg-gray-50 dark:bg-gray-900/30 font-bold' style='background-color: #f9fafb; font-weight: bold;'>";
            $breakdownHtml .= "<td colspan='2' class='border border-gray-300 dark:border-gray-700 p-2.5 text-right' style='border: 1px solid #d1d5db; padding: 10px; text-align: right;'>مجموع اقساط (" . count($installmentList) . " مرحله)</td>";
            $breakdownHtml .= "<td colspan='2' class='border border-gray-300 dark:border-gray-700 p-2.5 text-center' style='border: 1px solid #d1d5db; padding: 10px; text-align: center;'>" . $formatMoney($sumInstallments) . "</td>";
            $breakdownHtml .= "</tr></tfoot>";
        } else {
            $breakdownHtml .= "<tr><td colspan='4' class='border border-gray-300 dark:border-gray-700 p-3 text-center' style='border: 1px solid #d1d5db; padding: 12px; text-align: center;'>جزئیات اقساط یافت نشد</td></tr></tbody>";
        }

        $breakdownHtml .= '</table>';
        $tokens['installment_breakdown_table'] = $breakdownHtml;

        return $tokens;
    }
}
