<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Bank extends Model
{
    use HasFactory;

    protected $table = 'accounting_banks';

    protected $fillable = [
        'bank_name',
        'account_holder_name',
        'account_number',
        'card_number',
        'iban',
        'balance',
        'status',
        'color', // Added 'color' to fillable
    ];

    protected $casts = [
        'status' => 'boolean',
        'balance' => 'decimal:2',
    ];

    protected function displayInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                $displayName = $this->bank_name ?? 'بانک';
                if ($this->account_number) {
                    return "{$displayName} ({$this->account_number})";
                }
                if ($this->card_number) {
                    return "{$displayName} ({$this->card_number})";
                }
                if ($this->iban) {
                    return "{$displayName} (شبا: " . substr($this->iban, 0, 8) . "...)";
                }
                return $displayName;
            }
        );
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
