<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

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
        'host_id',
        'approval_status',
        'rejection_reason',
        'created_by',
        'agent_id', // Added agent_id
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

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
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

    public function host()
    {
        return $this->belongsTo(PropertyHost::class, 'host_id');
    }

    public function rentalConfig()
    {
        return $this->hasOne(PropertyRentalConfig::class, 'property_id');
    }

    public function seasonalPrices()
    {
        return $this->hasMany(PropertyRentalPrice::class, 'property_id');
    }

    public function rentalBlocks()
    {
        return $this->hasMany(PropertyRentalBlock::class, 'property_id');
    }

    public function building()
    {
        return $this->belongsTo(PropertyBuilding::class, 'building_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function attributeValues()
    {
        return $this->hasMany(PropertyAttributeValue::class);
    }

    public function getBedroomsAttribute($value)
    {
        if ($value !== null) return $value;

        if ($this->relationLoaded('attributeValues')) {
            $attr = $this->attributeValues->first(function($av) {
                return $av->attribute && (str_contains($av->attribute->name, 'اتاق خواب') || str_contains($av->attribute->name, 'تعداد خواب'));
            });
            if ($attr && $attr->value !== null && $attr->value !== '') {
                return $attr->value;
            }
        }

        if (isset($this->meta['details'])) {
            foreach ($this->meta['details'] as $k => $v) {
                $name = is_array($v) ? ($v['key'] ?? $v['name'] ?? $k) : $k;
                $val = is_array($v) ? ($v['value'] ?? null) : $v;
                if ((str_contains($name, 'اتاق خواب') || str_contains($name, 'تعداد خواب')) && $val !== null && $val !== '') {
                    return $val;
                }
            }
        }

        return null;
    }

    public function getBathroomsAttribute($value)
    {
        if ($value !== null) return $value;

        if ($this->relationLoaded('attributeValues')) {
            $attr = $this->attributeValues->first(function($av) {
                return $av->attribute && (str_contains($av->attribute->name, 'سرویس بهداشتی') || str_contains($av->attribute->name, 'تعداد حمام'));
            });
            if ($attr && $attr->value !== null && $attr->value !== '') {
                return $attr->value;
            }
        }

        if (isset($this->meta['details'])) {
            foreach ($this->meta['details'] as $k => $v) {
                $name = is_array($v) ? ($v['key'] ?? $v['name'] ?? $k) : $k;
                $val = is_array($v) ? ($v['value'] ?? null) : $v;
                if ((str_contains($name, 'سرویس بهداشتی') || str_contains($name, 'تعداد حمام')) && $val !== null && $val !== '') {
                    return $val;
                }
            }
        }

        return null;
    }

    public function getAreaFormattedAttribute()
    {
        $val = $this->area;
        if ($val === null || $val === '') {
            if ($this->relationLoaded('attributeValues')) {
                $attr = $this->attributeValues->first(function($av) {
                    return $av->attribute && str_contains($av->attribute->name, 'متراژ');
                });
                if ($attr && $attr->value) {
                    $val = $attr->value;
                }
            }
        }

        if ($val !== null && $val !== '') {
            if (is_numeric($val)) {
                return (floatval($val) == intval($val)) ? (string) intval($val) : rtrim(rtrim((string)$val, '0'), '.');
            }
            return (string) $val;
        }

        return null;
    }

    public function getSlugAttribute()
    {
        // Format: YmdHis-code (e.g., 20231027123045-1001)
        // If code is null, use id as fallback
        $identifier = $this->code ?? $this->id;
        $timestamp = $this->created_at ? $this->created_at->format('YmdHis') : now()->format('YmdHis');

        return "{$timestamp}-{$identifier}";
    }

    /**
     * Scope to filter properties visible to the current user.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeVisibleToUser(Builder $query)
    {
        $user = auth()->user();

        if (!$user) {
            // For guests (public view), show all approved published properties
            // If the property belongs to a host, that host must be active
            return $query->where('approval_status', 'approved')
                ->where(function($q) {
                    $q->whereNull('host_id')
                      ->orWhereHas('host', function($hq) {
                          $hq->where('status', 'active');
                      });
                });
        }

        // Super Admin, Admin or users with 'properties.view.all' or 'properties.manage' permission can see everything
        if ($user->hasRole(['super-admin', 'admin']) || $user->can('properties.view.all') || $user->can('properties.manage')) {
            return $query;
        }

        // Users can see properties they created, or where they are the assigned agent, or where they are the host
        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('agent_id', $user->id);

            // اگر کاربر میزبان ثبت‌شده باشد
            $host = PropertyHost::where('user_id', $user->id)->first();
            if ($host) {
                $q->orWhere('host_id', $host->id);
            }
        });
    }

    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PropertyRevision::class, 'property_id');
    }

    public function pendingRevision(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PropertyRevision::class, 'property_id')->where('status', 'pending')->latestOfMany();
    }

    public function latestRevision(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PropertyRevision::class, 'property_id')->latestOfMany();
    }
}
