<?php

namespace Modules\Services\App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    protected $table = 'services_statuses';
    protected $fillable = [
        'name',
        'color',
        'icon',
        'type',
        'is_final',
        'is_default',
        'is_readonly',
        'attributes',
        'allowed_transitions',
        'allowed_roles',
        'allowed_users',
        'sort_order',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'is_default' => 'boolean',
        'is_readonly' => 'boolean',
        'attributes' => 'array',
        'allowed_transitions' => 'array',
        'allowed_roles' => 'array',
        'allowed_users' => 'array',
    ];

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type)->orderBy('sort_order');
    }

    public function canTransitionTo(Status $target): bool
    {
        if (empty($this->allowed_transitions)) return true;
        return in_array($target->id, $this->allowed_transitions, true);
    }

    public static function defaultFor(string $type): ?self
    {
        return self::where('type', $type)->where('is_default', true)->first();
    }

    public function getAttributeData(string $key, $default = false): bool
    {
        // فراخوانی امن ستون دیتابیس برای جلوگیری از تداخل با متغیر داخلی لاراول
        $attrs = $this->getAttributeValue('attributes') ?? [];
        if (is_string($attrs)) {
            $attrs = json_decode($attrs, true) ?? [];
        }
        return (bool) ($attrs[$key] ?? $default);
    }

    public function convertsToInvoice(): bool
    {
        return $this->getAttributeData('converts_to_invoice');
    }

    public function locksInvoice(): bool
    {
        return $this->getAttributeData('locks_invoice');
    }

    public function allowsPayment(): bool
    {
        return $this->getAttributeData('allows_payment');
    }

    public function isSuccessfulPayment(): bool
    {
        return $this->getAttributeData('is_successful_payment');
    }

    public function isFailedPayment(): bool
    {
        return $this->getAttributeData('is_failed_payment');
    }
}
