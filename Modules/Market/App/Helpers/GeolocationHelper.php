<?php

namespace Modules\Market\App\Helpers;

use Illuminate\Support\Facades\Auth;
use Modules\Market\Entities\MarketSetting;

class GeolocationHelper
{
    /**
     * Get the current client location (province and city).
     * Returns ['province' => string, 'city' => string] or null.
     */
    public static function getClientLocation()
    {
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();
            $defaultAddress = $client->addresses()->where('is_default', true)->first() ?: $client->addresses()->first();
            if ($defaultAddress) {
                return [
                    'province' => $defaultAddress->province,
                    'city' => $defaultAddress->city,
                ];
            }
            return [
                'province' => null,
                'city' => null,
            ];
        }

        if (session()->has('client_location')) {
            return session('client_location');
        }

        return null;
    }

    /**
     * Check if a location needs to be collected from the client.
     */
    public static function isLocationRequired(): bool
    {
        if (!MarketSetting::getValue('orders.enable_geolocation_ordering', false)) {
            return false;
        }

        $loc = self::getClientLocation();
        if (!$loc || empty($loc['city'])) {
            return true;
        }

        return false;
    }
}
