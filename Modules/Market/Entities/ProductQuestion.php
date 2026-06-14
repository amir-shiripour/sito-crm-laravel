<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;
use App\Models\User;

class ProductQuestion extends Model
{
    protected $table = 'market_product_questions';

    protected $fillable = [
        'parent_id',
        'master_product_id',
        'client_id',
        'vendor_id',
        'user_id',
        'text',
        'status',
        'rejection_reason',
        'likes_count',
        'dislikes_count',
    ];

    public function masterProduct()
    {
        return $this->belongsTo(MasterProduct::class, 'master_product_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProductQuestion::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ProductQuestion::class, 'parent_id');
    }

    public function approvedReplies()
    {
        return $this->replies()->where('status', 'approved');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
