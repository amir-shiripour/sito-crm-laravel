<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $table = 'accounting_documents';

    protected $fillable = [
        'document_number',
        'document_date',
        'description',
        'status',
        'documentable_id',
        'documentable_type',
        'reference_number',
        'attachment',
        'cheque_id',
    ];

    protected $casts = [
        'document_date' => 'date',
    ];

    public function cheque(): BelongsTo
    {
        return $this->belongsTo(\Modules\Accounting\Entities\Cheque::class, 'cheque_id');
    }

    public function cheques()
    {
        return $this->morphToMany(\Modules\Accounting\Entities\Cheque::class, 'attachable', 'accounting_cheque_attachments');
    }

    public function category()
    {
        return $this->hasOneThrough(Category::class, Transaction::class, 'document_id', 'id', 'id', 'category_id');
    }

    public function fundAccount()
    {
        return $this->hasOneThrough(FundAccount::class, Transaction::class, 'document_id', 'id', 'id', 'fund_account_id');
    }

    public function client(): MorphTo
    {
        return $this->morphTo('documentable');
    }

    /**
     * Get the parent documentable model (e.g. Appointment, Order).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the transactions for the document.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the source document (origin of the accounting entry).
     */
    public function sourceDocument()
    {
        return $this->hasOne(SourceDocument::class);
    }

    /**
     * Get fund account balances after this document's transactions.
     */
    public function getFundAccountBalancesAttribute(): array
    {
        $balances = [];
        foreach ($this->transactions as $tx) {
            if ($tx->fund_account_id && $tx->fundAccount) {
                $balances[] = [
                    'fund_account_id' => $tx->fund_account_id,
                    'fund_account_name' => $tx->fundAccount->name,
                    'bank_name' => $tx->fundAccount->bank_display_name,
                    'balance_after' => $tx->account_balance_after,
                ];
            }
        }
        return $balances;
    }
}
