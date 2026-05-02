<?php
namespace Modules\Market\Entities;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model {
    protected $table = 'market_brands';
    protected $fillable = ['name', 'slug', 'code_prefix', 'logo', 'is_active'];
}
