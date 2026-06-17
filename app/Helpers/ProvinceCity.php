<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProvinceCity
{
    protected static ?array $data = null;

    /**
     * Load and cache the JSON data from the file.
     *
     * @return array
     */
    protected static function loadData(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $path = resource_path('data/iran-provinces-cities.json');

        if (!File::exists($path)) {
            Log::error('Province/City JSON file not found at path: ' . $path);
            return [];
        }

        $json = File::get($path);
        self::$data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Error decoding Province/City JSON: ' . json_last_error_msg());
            return [];
        }

        return self::$data;
    }

    /**
     * Get a list of all provinces.
     *
     * @return array
     */
    public static function getProvinces(): array
    {
        $data = self::loadData();
        return array_keys($data);
    }

    /**
     * Get a list of cities for a given province.
     *
     * @param string|null $provinceName
     * @return array
     */
    public static function getCities(?string $provinceName): array
    {
        if (empty($provinceName)) {
            return [];
        }

        $data = self::loadData();
        return $data[$provinceName] ?? [];
    }
}
