<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $table = 'accounting_transactions';

    protected $fillable = [
        'document_id',
        'category_id',
        'fund_account_id',
        'debit',
        'credit',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Get the document that owns the transaction.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the category that owns the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the fund account that owns the transaction.
     */
    public function fundAccount(): BelongsTo
    {
        return $this->belongsTo(FundAccount::class);
    }

    /**
     * Calculate the fund account balance right after this transaction.
     */
    public function getAccountBalanceAfterAttribute(): ?float
    {
        if (!$this->fund_account_id) {
            return null;
        }

        $balanceInRial = static::where('fund_account_id', $this->fund_account_id)
            ->where(function ($query) {
                $query->where('transaction_date', '<', $this->transaction_date)
                    ->orWhere(function ($q) {
                        $q->where('transaction_date', $this->transaction_date)
                            ->where('id', '<=', $this->id);
                    });
            })
            ->sum(\Illuminate\Support\Facades\DB::raw('debit - credit'));

        return (float) $balanceInRial;
    }

    /**
     * Get display bank / account title.
     */
    public function getBankDisplayNameAttribute(): string
    {
        return $this->fundAccount?->bank_display_name ?? '—';
    }
}
