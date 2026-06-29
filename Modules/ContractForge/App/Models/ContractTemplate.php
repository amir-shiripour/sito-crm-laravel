<?php

namespace Modules\ContractForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class ContractTemplate extends Model
{
    protected $table = 'contract_templates';

    protected $fillable = [
        'name',
        'entity_type',
        'blocks',
        'body',
        'css_style',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'blocks' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ContractRule::class, 'template_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'template_id');
    }
}
