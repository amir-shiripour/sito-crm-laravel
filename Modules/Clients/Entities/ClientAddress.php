<?php

namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientAddress extends Model
{
    use HasFactory;

    protected $table = 'client_addresses';

    protected $fillable = [
        'client_id',
        'title',
        'province',
        'city',
        'address',
        'postal_code',
        'lat',
        'lng',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'lat' => 'double',
        'lng' => 'double',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
