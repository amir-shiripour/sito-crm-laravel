<?php

namespace Modules\ClientCalls\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Clients\Entities\Client;

class ClientCall extends Model
{
    use HasFactory;

    protected $table = 'client_calls';

    protected $fillable = [
        'client_id',
        'user_id',
        'call_date',
        'call_time',
        'reason',
        'result',
        'status',
    ];

    protected $casts = [
        'call_date' => 'date',
        'call_time' => 'datetime', // فقط زمان
    ];

    // روابط
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * محدودسازی نمایش تماس‌ها بر اساس پرمیشن‌ها و نقش‌ها
     *
     * منطق:
     *  - اگر user اصلاً پرمیشن client-calls.view ندارد -> هیچ چیزی
     *  - اگر super-admin یا client-calls.view.all دارد -> همه
     *  - اگر client-calls.view.assigned دارد -> فقط تماس‌های مشتریانی که طبق
     *    scope visibleForUser روی Client برای او قابل مشاهده‌اند
     *  - اگر client-calls.view.own دارد -> فقط تماس‌هایی که خودش ثبت کرده (user_id)
     *  - در غیر این صورت: پیش‌فرض own
     */
    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        // اگر پرمیشن پایه دیدن تماس‌ها را ندارد، هیچ چیزی نشان نده
        if (! $user->can('client-calls.view')) {
            return $query->whereRaw('1 = 0');
        }

        // سوپر ادمین یا پرمیشن دیدن همه تماس‌ها
        if ($user->hasRole('super-admin') || $user->can('client-calls.view.all')) {
            return $query;
        }

        // دیدن تماس‌های مربوط به مشتریان قابل مشاهده برای این کاربر
        if ($user->can('client-calls.view.assigned')) {
            return $query->whereHas('client', function (Builder $q) use ($user) {
                $q->visibleForUser($user);
            });
        }

        // فقط تماس‌هایی که خود کاربر ثبت کرده
        if ($user->can('client-calls.view.own')) {
            return $query->where('user_id', $user->id);
        }

        // fallback: اگر فقط client-calls.view دارد و بقیه را ندارد => own
        return $query->where('user_id', $user->id);
    }

    /**
     * چک کردن این که آیا یک تماس خاص برای user قابل مشاهده است یا نه
     */
    public function isVisibleFor(User $user): bool
    {
        return static::visibleForUser($user)
            ->whereKey($this->getKey())
            ->exists();
    }
}
