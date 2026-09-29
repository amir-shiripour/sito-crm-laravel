<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class PropertyHost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'property_hosts';

    protected $fillable = [
        'user_id',
        'owner_id',
        'display_name',
        'slug',
        'phone',
        'avatar',
        'about',
        'shaba_number',
        'bank_name',
        'account_owner_name',
        'national_code',
        'national_card_image',
        'kyc_status',
        'kyc_rejection_reason',
        'status',
        'commission_rate',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(PropertyOwner::class, 'owner_id');
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'host_id');
    }

    public function activeProperties()
    {
        return $this->hasMany(Property::class, 'host_id')
            ->where('publication_status', 'published')
            ->where('approval_status', 'approved');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isKycApproved(): bool
    {
        return $this->kyc_status === 'approved';
    }

    public function getEffectiveCommissionRateAttribute(): float
    {
        if (!is_null($this->commission_rate)) {
            return (float) $this->commission_rate;
        }

        return (float) PropertySetting::get('rental_default_commission', 10);
    }
}
