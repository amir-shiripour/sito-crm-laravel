<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Carbon\Carbon;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'properties';

    protected $fillable = [
        'title',
        'code',
        'description',
        'listing_type',
        'property_type',
        'document_type',
        'building_id',
        'registered_at',
        'publication_status',
        'confidential_notes',
        'usage_type',
        'delivery_date',
        'price',
        'min_price',
        'advance_price',
        'deposit_price',
        'rent_price',
        'is_convertible',
        'convertible_with',
        'address',
        'latitude',
        'longitude',
        'area',
        'cover_image',
        'video',
        'status_id',
        'category_id',
        'owner_id',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'price' => 'decimal:0',
        'min_price' => 'decimal:0',
        'advance_price' => 'decimal:0',
        'deposit_price' => 'decimal:0',
        'rent_price' => 'decimal:0',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'delivery_date' => 'date',
        'registered_at' => 'date',
        'is_convertible' => 'boolean',
    ];

    const DOCUMENT_TYPES = [
        'mosh' => 'سند مشاع',
        'shesh_dang' => 'سند شش دانگ',
        'mafruz' => 'سند مفروز',
        'manguleh_dar' => 'سند منگوله دار',
        'tak_barg' => 'سند تک برگ',
        'ayan' => 'سند اعیان',
        'arseh' => 'سند عرصه',
        'vaghfi' => 'سند وقفی',
        'verasei' => 'سند ورثه‌ای',
        'almosana' => 'سند المثنی',
        'moarez' => 'سند معارض',
        'shoraei' => 'سند شورایی',
        'vekalati' => 'سند وکالتی',
        'bonchagh' => 'سند بنچاق',
        'rahni' => 'سند رهنی',
    ];

    const PUBLICATION_STATUSES = [
        'draft' => 'پیش‌نویس',
        'published' => 'منتشر شده',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function status()
    {
        return $this->belongsTo(PropertyStatus::class, 'status_id');
    }

    public function category()
    {
        return $this->belongsTo(PropertyCategory::class, 'category_id');
    }

    public function owner()
    {
        return $this->belongsTo(PropertyOwner::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function attributeValues()
    {
        return $this->hasMany(PropertyAttributeValue::class);
    }

    public function getSlugAttribute()
    {
        // Format: YmdHis-code (e.g., 20231027123045-1001)
        // If code is null, use id as fallback
        $identifier = $this->code ?? $this->id;
        $timestamp = $this->created_at ? $this->created_at->format('YmdHis') : now()->format('YmdHis');

        return "{$timestamp}-{$identifier}";
    }
}
