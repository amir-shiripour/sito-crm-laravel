<?php

namespace Modules\Services\App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ServiceTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'service_templates';

    protected $fillable = [
        'name',
        'description',
        'status_workflow',
    ];

    protected $casts = [
        'task_list' => 'array',
        'status_workflow' => 'array',
    ];

    public function customFields(): MorphMany
    {
        return $this->morphMany(CustomField::class, 'fieldable');
    }

    public function services(): ServiceTemplate|Builder|HasMany
    {
        return $this->hasMany(Service::class, 'template_id');
    }
}
