<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

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

    public function customValues()
    {
        return $this->hasMany(\App\Models\UserCustomValue::class);
    }

    public function marketVendor()
    {
        return $this->hasOne(\Modules\Market\Entities\Vendor::class, 'user_id');
    }

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
}
