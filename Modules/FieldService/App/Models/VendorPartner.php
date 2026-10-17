<?php

namespace Modules\FieldService\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Wallet\App\Traits\HasWallet;

class VendorPartner extends Model
{
    use HasFactory, SoftDeletes, HasWallet;

    protected $table = 'field_service_vendors';

    protected $fillable = [
        'user_id',
        'store_name',
        'owner_name',
        'mobile',
        'phone',
        'license_number',
        'province',
        'city',
        'address',
        'lat',
        'lng',
        'referral_commission_percent',
        'status',
        'notes',
        'meta',
    ];

    protected $casts = [
        'lat'                         => 'float',
        'lng'                         => 'float',
        'referral_commission_percent' => 'float',
        'meta'                        => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'vendor_id');
    }
}
