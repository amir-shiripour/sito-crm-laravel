<?php

namespace Modules\Services\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Settings\Entities\Setting;
use Modules\Clients\Entities\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;
use Modules\Services\App\Http\Models\Payment;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'service_invoices';

    protected $fillable = [
        'invoice_number',
        'proforma_invoice_number',
        'service_id',
        'customer_id',
        'created_by',
        'status_id',
        'client_name',
        'client_phone',
        'client_email',
        'issue_date',
        'tax_percent',
        'due_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
        'currency',
        'payment_mode',
        'payment_method',
        'payment_gateway',
        'installment_down_payment',
        'installment_steps',
        'installment_interest_rate',
        'installment_option_id',
        'installment_option_title',
        'installment_due_day',
        'installment_start_date',
        'installment_schedule',
        'transaction_ref',
        'paid_at',
        'notes',
        'meta',
        'converted_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'converted_at' => 'datetime',
        'subtotal' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'total' => 'integer',
        'paid_amount' => 'integer',
        'installment_down_payment' => 'integer',
        'installment_steps' => 'integer',
        'installment_interest_rate' => 'decimal:2',
        'installment_schedule' => 'array',
        'meta' => 'array',
        'tax_percent' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $inv) {
            if (is_null($inv->invoice_number) && is_null($inv->proforma_invoice_number)) {
                $inv->invoice_number ??= static::generateNumber();
            }

            if (is_null($inv->status_id)) {
                // تعیین وضعیت اولیه در زمان ایجاد فاکتور بدون وابستگی به شناسه
                $status = Status::where('type', 'payment')->where('name', 'در انتظار پرداخت')->first()
                    ?? Status::where('type', 'payment')->where('name', 'LIKE', '%انتظار%')->first()
                    ?? Status::where('type', 'payment')->first();
                $inv->status_id = $status?->id;
            }
        });

        static::saving(function (Invoice $invoice) {
            if ($invoice->isDirty('status_id')) {
                $invoice->unsetRelation('status');
            }

            // Do not recalculate paid_amount for merged/canceled invoices so they keep their historical values
            if (!$invoice->isMerged() && !$invoice->isCanceled()) {
                $invoice->paid_amount = $invoice->calculatePaidAmount();
            }
        });
    }

    public function calculatePaidAmount(): int
    {
        return $this->payments()->whereNotIn('status', ['canceled', 'pending'])->sum('amount');
    }

    public static function generateNumber(): string
    {
        $prefix = Setting::where('key', 'services_invoice_prefix')->value('value') ?? 'SRV-';
        $middle = Setting::where('key', 'services_invoice_middle_prefix')->value('value') ?? now()->format('Y');
        $suffix = Setting::where('key', 'services_invoice_suffix')->value('value') ?? '';
        $padding = (int)(Setting::where('key', 'services_invoice_padding')->value('value') ?? 4);

        $lastSeq = static::withTrashed()
            ->whereNotNull('invoice_number')
            ->whereRaw("invoice_number LIKE ?", ["{$prefix}{$middle}-%"])
            ->get()
            ->map(function($inv) use ($prefix, $middle, $suffix) {
                $numStr = str_replace([$prefix . $middle . '-', $suffix], '', $inv->invoice_number);
                return (int)$numStr;
            })->max() ?? 0;

        $seq = $lastSeq + 1;

        return $prefix . $middle . '-' . str_pad($seq, $padding, '0', STR_PAD_LEFT) . $suffix;
    }

    public static function generateProformaNumber(): string
    {
        $prefix = Setting::where('key', 'services_proforma_invoice_prefix')->value('value') ?? 'PI-';
        $middle = Setting::where('key', 'services_proforma_invoice_middle_prefix')->value('value') ?? now()->format('Y');
        $suffix = Setting::where('key', 'services_proforma_invoice_suffix')->value('value') ?? '';
        $padding = (int)(Setting::where('key', 'services_proforma_invoice_padding')->value('value') ?? 4);

        $lastSeq = static::withTrashed()
            ->whereNotNull('proforma_invoice_number')
            ->whereRaw("proforma_invoice_number LIKE ?", ["{$prefix}{$middle}-%"])
            ->get()
            ->map(function($inv) use ($prefix, $middle, $suffix) {
                $numStr = str_replace([$prefix . $middle . '-', $suffix], '', $inv->proforma_invoice_number);
                return (int)$numStr;
            })->max() ?? 0;

        $seq = $lastSeq + 1;

        return $prefix . $middle . '-' . str_pad($seq, $padding, '0', STR_PAD_LEFT) . $suffix;
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function validPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id')->where('status', '!=', 'canceled');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest();
    }

    public function isCanceled(): bool
    {
        $statusName = $this->status?->name ?? '';
        return str_contains($statusName, 'لغو') || str_contains(strtolower($statusName), 'cancel');
    }

    public function isMerged(): bool
    {
        $statusName = $this->status?->name ?? '';
        return str_contains($statusName, 'ادغام');
    }

    public function isEditable(): bool
    {
        if ($this->isCanceled() || $this->isMerged()) {
            return false;
        }

        if ($this->isPaid()) {
            return false;
        }

        if ($this->status && $this->status->locksInvoice()) {
            return false;
        }

        return true;
    }

    public function remainingAmount(): int
    {
        if ($this->isCanceled() || $this->isMerged()) {
            return 0;
        }
        return max(0, $this->total - $this->calculatePaidAmount());
    }

    public function isPaid(): bool
    {
        if ($this->isCanceled() || $this->isMerged()) {
            return false;
        }
        return max(0, $this->total - $this->calculatePaidAmount()) <= 0;
    }

    public function isOverdue(): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        if (empty($this->due_date)) {
            return false;
        }

        $gregorianDueDate = Carbon::parse($this->due_date);
        $isPastDueDate = $gregorianDueDate->isPast() && !$gregorianDueDate->isToday();

        $hasLatePayment = $this->payments()
            ->where('status', '!=', 'canceled')
            ->where('paid_at', '>', $gregorianDueDate)
            ->exists();

        return $isPastDueDate || $hasLatePayment;
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('paid_amount', '<', \DB::raw('total'))
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today());
    }

    public function updatePaymentStatus(bool $save = true): self
    {
        $this->paid_amount = $this->calculatePaidAmount();

        // Calculate paid_at dynamically
        if ($this->isPaid() && !$this->paid_at) {
            $this->paid_at = now();
        }

        if ($this->isPaid() && !$this->isCanceled() && !$this->isMerged() && empty($this->proforma_invoice_number)) {
            $paidStatus = Status::where('name', 'پرداخت شده')->first()
                ?? Status::where('name', 'LIKE', '%پرداخت شده%')->first();
            if ($paidStatus && $this->status_id !== $paidStatus->id) {
                $this->status_id = $paidStatus->id;
            }
        }

        if ($save) {
            $this->save();
        }

        return $this;
    }
}
