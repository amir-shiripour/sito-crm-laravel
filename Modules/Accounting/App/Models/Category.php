<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_categories';

    protected $fillable = [
        'parent_id',
        'title',
        'account_code',
        'level',
        'type',
        'status',
        'is_system',
        'is_treasury_related',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_system' => 'boolean',
        'is_treasury_related' => 'boolean',
        'type' => 'string',
        'level' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the transactions for the category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the fund accounts / treasury & banks linked to this category.
     */
    public function fundAccounts(): HasMany
    {
        return $this->hasMany(FundAccount::class, 'category_id');
    }
}
