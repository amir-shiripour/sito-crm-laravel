<?php

namespace Modules\Services\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Entities\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'service_orders';

    protected $fillable = [
        'order_number',
        'invoice_id',
        'service_id',
        'customer_id',
        'created_by',
        'status_id',
        'client_name',
        'client_phone',
        'client_email',
        'issue_date',
        'renewal_date',
        'billing_cycle',
        'first_payment_amount',
        'renewal_price',
        'renewal_price_type',
        'base_price_type',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'renewal_date' => 'date',
        'first_payment_amount' => 'integer',
        'renewal_price' => 'integer',
        'total_amount' => 'integer',
    ];

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

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoices()
    {
        if (empty($this->invoice_id) && empty($this->id)) {
            return Invoice::whereRaw('1 = 0');
        }

        return Invoice::where(function($query) {
            $hasCondition = false;

            if (!empty($this->invoice_id)) {
                $query->where('id', $this->invoice_id)
                      ->orWhereJsonContains('meta->merged_from_invoice_ids', (int)$this->invoice_id)
                      ->orWhere('meta->was_merged_into', (int)$this->invoice_id);
                $hasCondition = true;
            }

            if (!empty($this->id)) {
                if ($hasCondition) {
                    $query->orWhere('meta->source_order_id', (int)$this->id)
                          ->orWhere('meta->source_order_id', (string)$this->id);
                } else {
                    $query->where(function($q) {
                        $q->where('meta->source_order_id', (int)$this->id)
                          ->orWhere('meta->source_order_id', (string)$this->id);
                    });
                    $hasCondition = true;
                }
            }

            if (!$hasCondition) {
                $query->whereRaw('1 = 0');
            }
        })->latest();
    }

    protected static ?bool $directAdminActiveCache = null;
    protected static ?bool $domainManagerActiveCache = null;

    /**
     * Check if DirectAdmin module is installed, physically present and active.
     */
    public static function isDirectAdminActive(): bool
    {
        if (static::$directAdminActiveCache !== null) {
            return static::$directAdminActiveCache;
        }

        if (!class_exists('Modules\DirectAdmin\Entities\DaAccount')) {
            return static::$directAdminActiveCache = false;
        }

        if (class_exists(\Nwidart\Modules\Facades\Module::class)) {
            if (!\Nwidart\Modules\Facades\Module::has('DirectAdmin') || !\Nwidart\Modules\Facades\Module::isEnabled('DirectAdmin')) {
                return static::$directAdminActiveCache = false;
            }
        }

        try {
            if (class_exists(\App\Models\Module::class) && \Illuminate\Support\Facades\Schema::hasTable('modules')) {
                $dbMod = \App\Models\Module::where('slug', 'directadmin')->first();
                if ($dbMod && (!$dbMod->installed || !$dbMod->active)) {
                    return static::$directAdminActiveCache = false;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return static::$directAdminActiveCache = true;
    }

    /**
     * Check if DomainManager module is installed, physically present and active.
     */
    public static function isDomainManagerActive(): bool
    {
        if (static::$domainManagerActiveCache !== null) {
            return static::$domainManagerActiveCache;
        }

        if (!class_exists('Modules\DomainManager\Entities\DomainRecord')) {
            return static::$domainManagerActiveCache = false;
        }

        if (class_exists(\Nwidart\Modules\Facades\Module::class)) {
            if (!\Nwidart\Modules\Facades\Module::has('DomainManager') || !\Nwidart\Modules\Facades\Module::isEnabled('DomainManager')) {
                return static::$domainManagerActiveCache = false;
            }
        }

        try {
            if (class_exists(\App\Models\Module::class) && \Illuminate\Support\Facades\Schema::hasTable('modules')) {
                $dbMod = \App\Models\Module::where('slug', 'domainmanager')->first();
                if ($dbMod && (!$dbMod->installed || !$dbMod->active)) {
                    return static::$domainManagerActiveCache = false;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return static::$domainManagerActiveCache = true;
    }

    public function hostingAccount()
    {
        if (static::isDirectAdminActive()) {
            return $this->hasOne('Modules\DirectAdmin\Entities\DaAccount', 'order_id');
        }

        return $this->hasOne(self::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function domainRecord()
    {
        if (static::isDomainManagerActive()) {
            return $this->hasOne('Modules\DomainManager\Entities\DomainRecord', 'service_order_id');
        }

        return $this->hasOne(self::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function getCurrencyLabelAttribute(): string
    {
        if ($this->relationLoaded('invoice') && $this->invoice) {
            return $this->invoice->currency_label;
        }
        $curr = strtolower(\Modules\Settings\Entities\Setting::where('key', 'currency')->value('value') ?? 'rial');
        return in_array($curr, ['rial', 'irr', 'ریال']) ? 'ریال' : 'تومان';
    }

    public function getIsRenewalManualAttribute(): bool
    {
        return ($this->renewal_price_type ?? 'auto') === 'manual';
    }

    public function getCalculatedRenewalPriceAttribute(): float
    {
        if ($this->billing_cycle === 'one_time') {
            return 0.0;
        }

        if ($this->is_renewal_manual) {
            $price = (float)($this->renewal_price ?? 0);
        } else {
            $service = $this->service;
            if ($service && !empty($this->billing_cycle) && isset($service->renewal_prices[$this->billing_cycle]) && (float)$service->renewal_prices[$this->billing_cycle] > 0) {
                $price = (float)$service->renewal_prices[$this->billing_cycle];
            } else {
                $price = (float)($this->renewal_price ?? 0);
            }
        }

        if ($price <= 0) {
            $fallback = (float)($this->total_amount ?: ($this->first_payment_amount ?: ($this->service?->base_price ?: 0)));
            $price = $fallback;
        }

        return $price;
    }

    public function getEffectiveRenewalDateAttribute()
    {
        if ($this->renewal_date) {
            return $this->renewal_date;
        }

        if ($this->billing_cycle && $this->billing_cycle !== 'one_time') {
            $baseDate = $this->issue_date ?: ($this->invoice?->issue_date ?: $this->created_at);
            if ($baseDate) {
                try {
                    $carbon = \Carbon\Carbon::parse($baseDate);
                    return match ($this->billing_cycle) {
                        'monthly' => $carbon->addMonth(),
                        'quarterly' => $carbon->addMonths(3),
                        'semi_annual', 'semi-annual' => $carbon->addMonths(6),
                        'annual' => $carbon->addYear(),
                        'biennial' => $carbon->addYears(2),
                        'triennial' => $carbon->addYears(3),
                        default => null,
                    };
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        return null;
    }

    public function getRelatedInvoicesAttribute()
    {
        if (method_exists($this, 'invoices')) {
            try {
                $list = $this->invoices()->with(['status', 'payments'])->get();
                if ($list->isNotEmpty()) {
                    return $list;
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        if ($this->invoice) {
            $this->invoice->loadMissing(['status', 'payments']);
            return collect([$this->invoice]);
        }

        return collect();
    }
}


