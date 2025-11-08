<?php

namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'name', 'email', 'phone', 'notes', 'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
