<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\Transaction;
use Modules\Clients\Entities\Client;
use Morilog\Jalali\Jalalian;
use Illuminate\Database\Eloquent\Collection;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_invoices';

    protected $fillable = [
        'invoice_number',
        'client_id',
        'issue_date',
        'due_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function document()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function customer()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function client()
    {
        return $this->customer();
    }

    public function getTotalAmountAttribute()
    {
        return $this->total;
    }

    /**
     * Get all of the transactions for the invoice through the document.
     * This is an accessor, so you can call it like a property: $invoice->transactions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransactionsAttribute(): Collection
    {
        // Eager load the relations if not already loaded to prevent N+1 issues.
        $this->loadMissing('document.transactions');

        // Return the transactions from the document, or an empty collection if no document exists.
        return $this->document ? $this->document->transactions : new Collection();
    }

    /**
     * Accessor for the total paid amount.
     *
     * @return float
     */
    public function getPaidAmountAttribute(): float
    {
        if (!$this->document) {
            return 0;
        }
        // To calculate the paid amount, we sum the credits on the receivable account for this invoice's document.
        $receivableCatId = AccountingSetting::get('defaults.receivables_category_id');
        return $this->document->transactions()
            ->where('category_id', $receivableCatId)
            ->where('credit', '>', 0)
            ->sum('credit');
    }

    /**
     * Accessor for the remaining amount to be paid.
     *
     * @return float
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->total - $this->paid_amount;
    }

    /**
     * Get the dynamic title of the document based on its status.
     */
    public function getTitleAttribute(): string
    {
        return $this->status === 'draft' ? 'پیش-فاکتور' : 'فاکتور فروش';
    }

    /**
     * Get the display number (generates a temporary proforma number if draft).
     */
    public function getDisplayNumberAttribute(): string
    {
        if ($this->status !== 'draft' && $this->invoice_number) {
            return $this->invoice_number;
        }

        // Logic for generating temporary proforma number
        $prefix = AccountingSetting::getValue('proforma.numbering_prefix', 'PF');
        $separator = AccountingSetting::getValue('proforma.numbering_separator', '-');
        $length = (int)AccountingSetting::getValue('proforma.numbering_length', 4);
        $includeYear = (bool)AccountingSetting::getValue('proforma.numbering_include_year', true);

        // We use the ID as a base for the temporary number
        $paddedNumber = str_pad($this->id ?? 0, $length, '0', STR_PAD_LEFT);

        $year = '';
        if ($includeYear) {
             // Safe fallback if issue_date is not yet set or model not saved
             $date = $this->issue_date ?: now();
             $year = Jalalian::fromCarbon($date)->getYear() . $separator;
        }

        return $prefix . $separator . $year . $paddedNumber;
    }
}
