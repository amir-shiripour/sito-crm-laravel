<?php
namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'notes',
        'created_by',
    ];

    // ریلیشن‌ها
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // اگر بخواهی کاربران را به کلاینت متصل کنی:
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'client_user', 'client_id', 'user_id');
    }
}
