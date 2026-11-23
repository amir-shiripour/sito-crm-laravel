<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;

class WarehouseSetting extends Model
{
    protected $table = 'warehouse_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting || $setting->value === null) {
            return $default;
        }
        return $setting->value;
    }

    public static function setValue(string $key, mixed $value): void
    {
        $valString = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $valString]
        );
    }

    public static function isInventoryEnabled(): bool
    {
        return static::getValue('inventory_enabled', 'true') === 'true';
    }

    public static function isAssetsEnabled(): bool
    {
        return static::getValue('assets_enabled', 'true') === 'true';
    }

    public static function isMarketIntegrationEnabled(): bool
    {
        return static::getValue('market_integration_enabled', 'false') === 'true';
    }
}
