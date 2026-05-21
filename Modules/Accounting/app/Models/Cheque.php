<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Clients\Entities\Client;

class Cheque extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id', // Added missing fillable property
        'client_id',
        'bank_id',
        'type',
        'amount',
        'issue_date',
        'due_date',
        'cheque_number',
        'sayyad_id',
        'bank_name',
        'branch_name',
        'payee_name',
        'status',
        'description',
        'reconciliation_date', // تاریخ وصول
        'reconciled_bank_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'reconciliation_date' => 'date', // کست کردن تاریخ وصول
        'amount' => 'decimal:2',
    ];

    /**
     * Get the client that owns the cheque.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the bank that the cheque was reconciled with.
     */
    public function reconciledBank()
    {
        return $this->belongsTo(Bank::class, 'reconciled_bank_id');
    }

    /**
     * Check if the cheque is reconciled (passed).
     *
     * @return bool
     */
    public function isReconciled(): bool
    {
        return $this->status === 'passed';
    }
}
