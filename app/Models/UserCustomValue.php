<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCustomValue extends Model
{
    protected $fillable = ['user_id','field_name','value'];
}
