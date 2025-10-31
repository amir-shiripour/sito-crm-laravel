<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Theme extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'view_path', // مسیر پوشه ویوها (مثال: themes.modern-corporate)
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * The modules that are required for this theme.
     * (رابطه چند به چند با ماژول‌ها)
     */
    public function requiredModules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'theme_module_requirements');
    }
}

