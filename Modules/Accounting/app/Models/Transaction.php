<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Accounting\App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'accounting_transactions';

    protected $fillable = [
        'invoice_id',
        'bank_id', // Renamed from to_bank_id for clarity
        'amount',
        'type', // 'income' or 'expense'
        'payment_method',
        'reference_code',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * The invoice associated with this transaction.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The bank account where the transaction was recorded.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
