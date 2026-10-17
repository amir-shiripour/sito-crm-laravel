<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicianSkill extends Model
{
    use HasFactory;

    protected $table = 'field_service_technician_skills';

    protected $fillable = [
        'technician_id',
        'category_id',
        'is_verified',
        'certificate_number',
        'experience_years',
    ];

    protected $casts = [
        'is_verified'      => 'boolean',
        'experience_years' => 'integer',
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
