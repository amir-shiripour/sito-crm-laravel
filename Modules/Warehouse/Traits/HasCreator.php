<?php

namespace Modules\Warehouse\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Warehouse\Support\WarehouseUserVisibility;

trait HasCreator
{
    /**
     * Automatically set created_by server-side when creating records.
     */
    public static function bootHasCreator(): void
    {
        static::creating(function ($model) {
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCreatorNameAttribute(): string
    {
        return WarehouseUserVisibility::displayUserName($this->creator, null, WarehouseUserVisibility::MASKED_LABEL);
    }
}
