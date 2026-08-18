<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Modules\Booking\App\Models\DoctorProfile;
use Modules\Clients\Entities\Client; // 💡 مدل Client اضافه شد
use Modules\Market\App\Models\Order;
use Nwidart\Modules\Facades\Module;
use Spatie\Permission\Traits\HasRoles;
use Modules\Booking\App\Models\DoctorMedia;
use Modules\Market\Traits\HasMarketVendor;
use Modules\Wallet\App\Traits\HasWallet;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use HasMarketVendor;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * The clients that belong to the user.
     */
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_user', 'user_id', 'client_id');
    }

    // 💡 رابطه orders حذف شد چون دیگر مستقیم نیست.

    public function customValues()
    {
        return $this->hasMany(\App\Models\UserCustomValue::class);
    }

    // Relationships and accessors for market vendor are loaded via HasMarketVendor trait.

    /**
     * کاربرانی که این کاربر به آن‌ها متصل است (مثلاً پزشکانی که پذیرش به آن‌ها وصل است)
     */
    public function superiors()
    {
        return $this->belongsToMany(User::class, 'user_relationships', 'user_id', 'related_user_id')
                    ->withPivot('relation_type')
                    ->withTimestamps();
    }

    /**
     * کاربرانی که به این کاربر متصل هستند (مثلاً پذیرش‌هایی که به این پزشک وصل هستند)
     */
    public function subordinates()
    {
        return $this->belongsToMany(User::class, 'user_relationships', 'related_user_id', 'user_id')
                    ->withPivot('relation_type')
                    ->withTimestamps();
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        /*
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
        */
        return asset('img/profile-sir.png');
    }

    public function profile():HasOne
    {
        return $this->hasOne(DoctorProfile::class,'user_id');

    }
    public function canAccessDoctorTab(): bool
    {
        if (!Module::has('Booking') || !Module::isEnabled('Booking')) {
            return false;
        }

        if ($this->hasRole('doctor')) {
            return true;
        }

        try {
            if (!class_exists(\Modules\Booking\Entities\BookingSetting::class)) {
                return false;
            }

            $settings = \Modules\Booking\Entities\BookingSetting::current();
            if (!$settings) {
                return false;
            }

            $rolesInput = $settings->allowed_roles;
            if (is_string($rolesInput)) {
                $decoded = json_decode($rolesInput, true);
                $rolesInput = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', trim($rolesInput), -1, PREG_SPLIT_NO_EMPTY);
            }

            if (!is_array($rolesInput) || empty($rolesInput)) {
                return false;
            }

            $roleIds = [];
            $roleNames = [];
            foreach ($rolesInput as $v) {
                if (is_numeric($v) && (int)$v > 0) {
                    $roleIds[] = (int)$v;
                } elseif (is_string($v) && trim($v) !== '') {
                    $roleNames[] = trim($v);
                }
            }

            if (empty($roleIds) && empty($roleNames)) {
                return false;
            }

            return $this->roles->contains(function ($role) use ($roleIds, $roleNames) {
                return in_array((int)$role->id, $roleIds, true) || in_array($role->name, $roleNames, true);
            });
        } catch (\Throwable $e) {
            return false;
        }
    }
    public function doctorMedia()
    {
        return $this->hasMany(DoctorMedia::class, 'user_id', 'id');
    }
}
