<?php

namespace Modules\FieldService\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Clients\Entities\Client;
use Modules\Wallet\App\Traits\HasWallet;

class VendorPartner extends Model
{
    use HasFactory, SoftDeletes, HasWallet;

    protected $table = 'field_service_vendors';

    protected $fillable = [
        'client_id',
        'user_id',
        'store_name',
        'owner_name',
        'mobile',
        'phone',
        'license_number',
        'referral_code',
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

    protected static function booted()
    {
        static::creating(function (VendorPartner $vendor) {
            if (empty($vendor->referral_code)) {
                $vendor->referral_code = self::generateUniqueReferralCode();
            }
        });
    }

    public static function generateUniqueReferralCode(): string
    {
        do {
            $code = 'NB-' . strtoupper(Str::random(5));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'vendor_id');
    }

    public function getWalletBalanceAttribute(): float
    {
        if ($this->client && method_exists($this->client, 'wallet')) {
            return (float) ($this->client->wallet?->balance ?? 0);
        }

        return (float) ($this->wallet?->balance ?? 0);
    }
}
