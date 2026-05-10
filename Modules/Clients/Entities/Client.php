<?php

namespace Modules\Clients\Entities;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Client extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable;

    protected $table = 'clients';

    protected $fillable = [
        'username',
        'full_name',
        'email',
        'phone',
        'national_code',
        'case_number',
        'notes',
        'status_id',
        'meta',
        'created_by',
        'password',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ریلیشن‌ها
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'client_user', 'client_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * محدود کردن کوئری کلاینت‌ها بر اساس نقش/پرمیشن کاربر
     */
    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        // اگر حتی مجوز پایه‌ی مشاهده مشتریان را ندارد، هیچ موردی برنگردان
        if (! $user->can('clients.view')) {
            return $query->whereRaw('1 = 0');
        }

        // 1) سوپر ادمین همیشه همه را می‌بیند
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        // 2) اگر اجازه‌ی دیدن همه یا مدیریت دارد → همه کلاینت‌ها
        if ($user->can('clients.view.all') || $user->can('clients.manage')) {
            return $query;
        }

        // دریافت لیست ID سرپرستان/پزشکان متصل به این کاربر
        $superiorIds = $user->superiors()->pluck('users.id')->toArray();
        $userIds = array_merge([$user->id], $superiorIds);

        // 3) اگر اجازه‌ی دیدن کلاینت‌های assign‌شده دارد
        if ($user->can('clients.view.assigned')) {
            return $query->where(function (Builder $q) use ($userIds) {
                // کلاینت‌هایی که خودش یا سرپرستانش ساخته‌اند
                $q->whereIn('created_by', $userIds)
                    // کلاینت‌هایی که به خودش یا سرپرستانش assign شده‌اند
                    ->orWhereHas('users', function (Builder $sub) use ($userIds) {
                        $sub->whereIn('users.id', $userIds);
                    });
            });
        }

        // 4) اگر اجازه‌ی دیدن کلاینت‌های خودش را دارد
        if ($user->can('clients.view.own')) {
            return $query->whereIn('created_by', $userIds);
        }

        // 5) اگر فقط clients.view ساده را دارد و هیچ‌کدام از بالا فعال نیست
        // رفتار محافظه‌کارانه: فقط کلاینت‌هایی که خودش یا سرپرستانش ایجاد کرده‌اند
        return $query->whereIn('created_by', $userIds);
    }

    public function isVisibleFor(User $user): bool
    {
        return static::query()
            ->visibleForUser($user)
            ->whereKey($this->getKey())
            ->exists();
    }

    public function status()
    {
        return $this->belongsTo(ClientStatus::class, 'status_id');
    }

    public function calls()
    {
        return $this->hasMany(\Modules\ClientCalls\Entities\ClientCall::class, 'client_id')
            ->orderByDesc('call_date')
            ->orderByDesc('call_time');
    }

    public function followUps()
    {
        return $this->hasMany(\Modules\FollowUps\Entities\FollowUp::class, 'related_id')
            ->where('related_type', \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT)
            ->orderByDesc('due_at')
            ->orderByDesc('created_at');
    }
}
