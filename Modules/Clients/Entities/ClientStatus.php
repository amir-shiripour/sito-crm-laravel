<?php
namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;

class ClientStatus extends Model
{
    protected $fillable = [
        'key',
        'label',
        'color',
        'is_system',
        'is_active',
        'show_in_quick',
        'sort_order',
        'allowed_from',
    ];

    protected $casts = [
        'is_system'   => 'bool',
        'is_active'   => 'bool',
        'show_in_quick' => 'bool',
        'allowed_from'  => 'array',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'status_id');
    }

    // فقط وضعیت‌های فعال
    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
