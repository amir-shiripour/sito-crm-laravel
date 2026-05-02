<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Vendor extends Model
{
    protected $table = 'market_vendors';

    protected $fillable = [
        'user_id', 'store_name', 'slug', 'logo', 'cover_image', 'support_phone', 'description',
        'legal_type', 'national_code', 'economic_code', // KYC
        'shaba_number', 'account_owner_name', 'bank_name', // مالی
        'contract_accepted_at', 'kyc_status', 'status', 'commission_rate', 'kyc_rejection_reason'
    ];

    protected $casts = [
        'contract_accepted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'vendor_id');
    }

    // رابطه با آدرس‌ها
    public function addresses()
    {
        return $this->hasMany(VendorAddress::class, 'vendor_id');
    }

    // رابطه با مدارک
    public function documents()
    {
        return $this->hasMany(VendorDocument::class, 'vendor_id');
    }
}
