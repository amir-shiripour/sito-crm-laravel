<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounting\App\Helpers\AccountingWalletHelper;

class FundAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_fund_accounts';

    protected $fillable = [
        'name',
        'type',
        'category_id', // Added category_id
        'core_gateway_id',
        'bank_name',
        'branch_name',
        'account_holder_name',
        'account_number',
        'card_number',
        'iban',
        'currency',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the category that the fund account belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if this fund account represents the aggregated wallet account.
     */
    public function isWalletAccount(): bool
    {
        if (!AccountingWalletHelper::isWalletEnabled()) {
            return false;
        }

        return str_contains($this->notes ?? '', 'wallet_aggregated_account')
            || str_contains($this->notes ?? '', 'wallet_id:');
    }

    /**
     * Get current balance in Rials (IRR).
     */
    public function getCurrentBalanceAttribute(): float
    {
        if ($this->isWalletAccount() && AccountingWalletHelper::isWalletEnabled() && class_exists(\Modules\Wallet\App\Models\Wallet::class)) {
            return \Modules\Wallet\App\Models\Wallet::getTotalBalance();
        }

        if ($this->relationLoaded('transactions')) {
            return (float) ($this->transactions->sum('debit') - $this->transactions->sum('credit'));
        }

        return (float) ($this->transactions()->sum('debit') - $this->transactions()->sum('credit'));
    }

    /**
     * Get display title combining bank name and account title.
     */
    public function getBankDisplayNameAttribute(): string
    {
        if (!empty($this->bank_name)) {
            if ($this->name && str_contains($this->name, $this->bank_name)) {
                return $this->name;
            }
            return $this->bank_name . ' (' . $this->name . ')';
        }
        return $this->name ?? '—';
    }
}
