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
        'type', // 💡 اضافه شد
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 💡 NEW: Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($warehouse) {
            if (empty($warehouse->code)) {
                $warehouse->code = self::generateUniqueCode($warehouse->vendor_id);
            }
        });
    }

    /**
     * 💡 NEW: Method to generate a unique warehouse code.
     */
    public static function generateUniqueCode($vendorId = null): string
    {
        if ($vendorId) {
            // انبار متعلق به فروشنده
            $prefix = "WH-VND{$vendorId}-";
            $lastWarehouse = self::where('vendor_id', $vendorId)->orderBy('id', 'desc')->first();
            $counter = $lastWarehouse ? (int) substr(strrchr($lastWarehouse->code, "-"), 1) + 1 : 1;
        } else {
            // انبار مرکزی
            $prefix = "WH-MAIN-";
            $lastWarehouse = self::whereNull('vendor_id')->orderBy('id', 'desc')->first();
            $counter = $lastWarehouse ? (int) substr(strrchr($lastWarehouse->code, "-"), 1) + 1 : 1;
        }

        $newCode = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);

        // بررسی یکتا بودن (در موارد نادر Race Condition)
        while (self::where('code', $newCode)->exists()) {
            $counter++;
            $newCode = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        }

        return $newCode;
    }


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
