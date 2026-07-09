<?php

declare(strict_types=1);

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SalesLossReason extends Model
{
    protected $table = 'sales_loss_reasons';

    protected $fillable = ['reason_key', 'reason_text', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function deals(): HasMany
    {
        return $this->hasMany(SalesDeal::class, 'loss_reason_id');
    }
}
