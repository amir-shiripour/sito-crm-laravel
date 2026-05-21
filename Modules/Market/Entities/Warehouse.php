<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Market\Entities\Vendor;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'market_warehouses';

    protected $fillable = [
        'vendor_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function transactions()
    {
        return $this->hasMany(WarehouseTransaction::class);
    }

    protected static function newFactory()
    {
        // return \Modules\Market\Database\factories\WarehouseFactory::new();
    }
}
