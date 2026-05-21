<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Clients\Entities\Client;
use Morilog\Jalali\Jalalian;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (in_array($value, ['unpaid', 'partially_paid']) && $this->due_date && $this->due_date->isPast()) {
                    return 'overdue';
                }
                return $value;
            }
        );
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->getRawOriginal('status')) {
            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'partially_paid' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
            'pending_review' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'unpaid' => $this->due_date && $this->due_date->isPast()
                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'refunded', 'bad_debt' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function cheques(): HasMany
    {
        return $this->hasMany(Cheque::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(\Modules\Accounting\App\Models\Document::class, 'documentable');
    }

    /**
     * Get the next invoice number based on settings.
     *
     * @return string
     */
    public static function getNextInvoiceNumber(): string
    {
        $prefix = AccountingSetting::getValue('numbering.prefix', 'INV');
        $separator = AccountingSetting::getValue('numbering.separator', '-');
        $length = (int) AccountingSetting::getValue('numbering.length', 4);
        $includeYear = (bool) AccountingSetting::getValue('numbering.include_year', true);

        $year = $includeYear ? (new Jalalian(now()->year, now()->month, now()->day))->format('y') : null;

        // Find the last invoice number with the same format (prefix and year if applicable)
        $query = self::query();
        if ($year) {
            $query->where('invoice_number', 'like', "{$prefix}{$separator}{$year}{$separator}%");
        } else {
            $query->where('invoice_number', 'like', "{$prefix}{$separator}%");
        }

        $latestInvoice = $query->orderBy('invoice_number', 'desc')->first();

        $newNumericPart = 1;
        if ($latestInvoice) {
            $parts = explode($separator, $latestInvoice->invoice_number);
            $lastNumericPart = (int) end($parts);
            $newNumericPart = $lastNumericPart + 1;
        }

        $paddedNumber = str_pad($newNumericPart, $length, '0', STR_PAD_LEFT);

        $finalParts = [$prefix];
        if ($year) {
            $finalParts[] = $year;
        }
        $finalParts[] = $paddedNumber;

        return implode($separator, $finalParts);
    }
}
