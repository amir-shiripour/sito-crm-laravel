<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Accounting\App\Models\Bank; // Corrected namespace
use Modules\Clients\Entities\Client;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'status',
        'description',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
