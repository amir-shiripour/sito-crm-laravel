<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'gateway',
        'authority',
        'ref_id',
        'status',
        'description',
        'callback_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
