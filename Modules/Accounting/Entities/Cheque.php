<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Clients\Entities\Client;

class Cheque extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_cheques';

    protected $fillable = [
        'type',
        'amount',
        'payee_name',
        'cheque_number',
        'bank_name',
        'bank_branch',
        'issue_date',
        'due_date',
        'description',
        'client_id',
        'status',
        'image_path',
        'registered_by_user_id',
        'deposited_fund_account_id',
        'cleared_fund_account_id',
        'related_invoice_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function expenseDocuments()
    {
        return $this->hasMany(Document::class, 'cheque_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function customer()
    {
        return $this->client();
    }

    public function depositedFundAccount()
    {
        return $this->belongsTo(FundAccount::class, 'deposited_fund_account_id');
    }

    public function clearedFundAccount()
    {
        return $this->belongsTo(FundAccount::class, 'cleared_fund_account_id');
    }

    public function relatedInvoice()
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function attachedInvoices()
    {
        return $this->morphedByMany(Invoice::class, 'attachable', 'accounting_cheque_attachments');
    }

    public function attachedDocuments()
    {
        return $this->morphedByMany(Document::class, 'attachable', 'accounting_cheque_attachments');
    }
}
