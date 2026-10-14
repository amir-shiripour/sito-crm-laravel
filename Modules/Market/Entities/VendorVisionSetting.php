<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorVisionSetting extends Model
{
    use HasFactory;

    protected $table = 'market_vendor_vision_settings';

    protected $fillable = [
        'vendor_id',
        'is_active',
        'server_url',
        'ws_username',
        'ws_password',
        'admin_pass',
        'default_visitor_code',
        'default_warehouse_code',
        'default_customer_code',
        'auto_sync_stock',
        'auto_dispatch_order',
        'last_sync_at',
        'last_sync_status',
        'last_error_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_sync_stock' => 'boolean',
        'auto_dispatch_order' => 'boolean',
        'ws_password' => 'encrypted',
        'admin_pass' => 'encrypted',
        'last_sync_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
