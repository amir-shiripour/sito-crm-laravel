<?php

namespace Modules\Clients\App\Livewire\Portal;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\Clients\Entities\ClientAddress;

class ClientAddressManager extends Component
{
    public $addresses = [];
    public $showForm = false;
    public $addressId = null;

    // Form fields
    public $title = '';
    public $province = '';
    public $city = '';
    public $address = '';
    public $postal_code = '';
    public $lat = 35.6892; // Default to Tehran lat
    public $lng = 51.3890; // Default to Tehran lng
    public $is_default = false;

    // Search fields
    public string $searchQuery = '';
    public array $searchResults = [];

    // Map config
    public $mapProvider = 'neshan';
    public $mapApiKey = '';

    public function updatedSearchQuery($query)
    {
        if (strlen($query) < 3) {
            $this->searchResults = [];
            return;
        }

        $results = [];

        if (interface_exists(\Modules\Market\App\Services\Map\MapServiceInterface::class) && app()->bound(\Modules\Market\App\Services\Map\MapServiceInterface::class)) {
            $mapService = app(\Modules\Market\App\Services\Map\MapServiceInterface::class);
            if (!empty($mapService->getApiKey())) {
                $results = $mapService->search($query, $this->lat, $this->lng);
            }
        }

        if (empty($results)) {
            $results = $this->fallbackSearch($query);
        }

        $this->searchResults = $results;
    }

    protected function fallbackSearch($query)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'Laravel-CRM-Map-App'
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/search", [
                'q' => $query,
                'format' => 'json',
                'accept-language' => 'fa,en',
                'limit' => 10
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $results = [];
                foreach ($data as $item) {
                    $results[] = [
                        'title' => $item['name'] ?? $item['display_name'] ?? '',
                        'address' => $this->sanitizeAddress($item['display_name'] ?? '', '', ''),
                        'lat' => (float)($item['lat'] ?? 0),
                        'lng' => (float)($item['lon'] ?? 0),
                    ];
                }
                return $results;
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        return [];
    }

    public function selectSearchResult($lat, $lng, $title = '')
    {
        $this->lat = (float)$lat;
        $this->lng = (float)$lng;
        $this->searchResults = [];
        $this->searchQuery = $title;

        $this->fetchAddressFromCoordinates($lat, $lng);

        $this->dispatch('mapMoveTo', lat: $lat, lng: $lng);
    }

    protected $rules = [
        'title' => 'required|string|max:100',
        'province' => 'required|string|max:100',
        'city' => 'required|string|max:100',
        'address' => 'required|string',
        'postal_code' => 'required|string|max:10',
        'lat' => 'required|numeric',
        'lng' => 'required|numeric',
        'is_default' => 'boolean',
    ];

    protected $validationAttributes = [
        'title' => 'عنوان آدرس',
        'province' => 'استان',
        'city' => 'شهر',
        'address' => 'نشانی دقیق',
        'postal_code' => 'کد پستی',
        'lat' => 'عرض جغرافیایی',
        'lng' => 'طول جغرافیایی',
    ];

    public function mount()
    {
        $this->loadAddresses();

        if (class_exists(\Modules\Market\Entities\MarketSetting::class)) {
            $this->mapProvider = \Modules\Market\Entities\MarketSetting::getValue('map.provider', 'neshan');
            $this->mapApiKey = \Modules\Market\Entities\MarketSetting::getValue('map.api_key', '');
        }
    }

    public function loadAddresses()
    {
        $client = auth('client')->user();
        if ($client) {
            $this->addresses = $client->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();
        } else {
            $this->addresses = [];
        }
    }

    public function openAddForm()
    {
        $this->resetForm();
        $this->showForm = true;
        // Dispatch event to re-initialize map
        $this->dispatch('initMap', lat: $this->lat, lng: $this->lng);
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->addressId = null;
        $this->title = '';
        $this->province = '';
        $this->city = '';
        $this->address = '';
        $this->postal_code = '';
        $this->lat = 35.6892;
        $this->lng = 51.3890;
        $this->is_default = false;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->resetErrorBag();
    }

    public function fetchAddressFromCoordinates($lat, $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $geoData = [];

        if (interface_exists(\Modules\Market\App\Services\Map\MapServiceInterface::class) && app()->bound(\Modules\Market\App\Services\Map\MapServiceInterface::class)) {
            $mapService = app(\Modules\Market\App\Services\Map\MapServiceInterface::class);
            $geoData = $mapService->reverseGeocode($lat, $lng);
        }

        // Fallback to OpenStreetMap Nominatim if primary geocode failed or returned empty address
        if (empty($geoData['address'])) {
            $geoData = $this->fallbackGeocode($lat, $lng);
        }

        if (!empty($geoData['address'])) {
            $rawProvince = $geoData['province'] ?? $this->province;
            $rawCity = $geoData['city'] ?? $this->city;

            // Normalize Province
            $rawProvince = trim(str_replace(['استان ', ' استان', ' Province'], '', $rawProvince));

            // Normalize City
            $rawCity = trim(str_replace(['شهر ', ' شهر', ' City', 'شهرستان ', ' بخش '], '', $rawCity));

            $this->province = $rawProvince;
            $this->city = $rawCity;

            // Sanitize address to make it Snapp-like
            $this->address = $this->sanitizeAddress($geoData['address'], $rawProvince, $rawCity);

            if (isset($geoData['data']['postal_code']) && !empty($geoData['data']['postal_code'])) {
                $this->postal_code = $geoData['data']['postal_code'];
            } elseif (isset($geoData['postal_code']) && !empty($geoData['postal_code'])) {
                $this->postal_code = $geoData['postal_code'];
            }
        }
    }

    protected function sanitizeAddress($address, $province, $city)
    {
        if (empty($address)) return '';

        // Remove country
        $address = preg_replace('/\b(ایران|iran)\b/iu', '', $address);

        // Remove province
        if (!empty($province)) {
            $provinceClean = str_replace(['استان ', ' Province'], '', $province);
            $address = preg_replace('/\b(' . preg_quote($province, '/') . '|' . preg_quote($provinceClean, '/') . '|استان\s+' . preg_quote($provinceClean, '/') . ')\b/iu', '', $address);
        }
        $address = preg_replace('/استان\s+[\p{L}\s]+/u', '', $address);

        // Remove county / district / municipality administrative noise
        $address = preg_replace('/شهرستان\s+[\p{L}\s]+/u', '', $address);
        $address = preg_replace('/بخش\s+مرکزی\s+[\p{L}\s]+/u', '', $address);
        $address = preg_replace('/بخش\s+[\p{L}\s]+/u', '', $address);
        $address = preg_replace('/شهرداری\s+منطقه\s+[\d\p{L}\s]+(ناحیه\s+[\d\p{L}\s]+)?/u', '', $address);
        $address = preg_replace('/منطقه\s+\d+(\s+شهر\s+[\p{L}\s]+)?/u', '', $address);

        // Remove city name if it appears in address
        if (!empty($city)) {
            $cityClean = str_replace(['شهر ', ' City'], '', $city);
            $address = preg_replace('/\b(' . preg_quote($city, '/') . '|' . preg_quote($cityClean, '/') . '|شهر\s+' . preg_quote($cityClean, '/') . ')\b/iu', '', $address);
        }

        // Remove postal code
        $address = preg_replace('/\b\d{5}-?\d{5}\b/u', '', $address);

        // Clean up duplicate commas, spaces and trailing separators
        $address = preg_replace('/[،,]\s*[،,]+/u', '، ', $address);
        $address = preg_replace('/^\s*[،,]\s*|\s*[،,]\s*$/u', '', $address);

        // Split by comma, trim each part, remove empty/duplicates, and join back
        $rawParts = array_map('trim', explode('،', str_replace(',', '،', $address)));
        $filteredParts = [];
        foreach ($rawParts as $part) {
            if (!empty($part) && !in_array($part, $filteredParts)) {
                $filteredParts[] = $part;
            }
        }

        return implode('، ', $filteredParts);
    }

    protected function fallbackGeocode($lat, $lng)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'Laravel-CRM-Map-App'
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse", [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'accept-language' => 'fa,en'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $addressData = $data['address'] ?? [];

                $province = $addressData['state'] ?? '';
                $province = trim(str_replace(['استان ', ' Province'], '', $province));

                $city = $addressData['city'] ?? $addressData['town'] ?? $addressData['suburb'] ?? $addressData['village'] ?? '';
                $city = trim(str_replace(['شهر ', ' City', 'شهرستان '], '', $city));

                $addressParts = [];
                if (!empty($addressData['suburb']) && $addressData['suburb'] !== $city) {
                    $addressParts[] = $addressData['suburb'];
                }
                if (!empty($addressData['neighbourhood'])) {
                    $addressParts[] = $addressData['neighbourhood'];
                }
                if (!empty($addressData['quarter'])) {
                    $addressParts[] = $addressData['quarter'];
                }
                if (!empty($addressData['road'])) {
                    $addressParts[] = $addressData['road'];
                }
                if (!empty($addressData['amenity'])) {
                    $addressParts[] = $addressData['amenity'];
                }
                if (!empty($addressData['house_number'])) {
                    $addressParts[] = 'پلاک ' . $addressData['house_number'];
                }

                if (!empty($addressParts)) {
                    $formattedAddress = implode('، ', array_unique($addressParts));
                } else {
                    $formattedAddress = $this->sanitizeAddress($data['display_name'] ?? '', $province, $city);
                }

                $postcode = $addressData['postcode'] ?? '';
                if (!empty($postcode)) {
                    $postcode = preg_replace('/\D/', '', $postcode);
                }

                return [
                    'province' => $province,
                    'city' => $city,
                    'address' => $formattedAddress,
                    'formatted' => $formattedAddress,
                    'postal_code' => $postcode,
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            // Silence fallback errors
        }

        return [
            'province' => '',
            'city' => '',
            'address' => '',
            'formatted' => ''
        ];
    }

    public function saveAddress()
    {
        $this->validate();

        $client = auth('client')->user();
        if (!$client) {
            return;
        }

        if ($this->is_default) {
            // Unset other defaults
            $client->addresses()->update(['is_default' => false]);
        }

        // If it's the first address, force it to be default
        if ($client->addresses()->count() === 0) {
            $this->is_default = true;
        }

        if ($this->addressId) {
            $address = $client->addresses()->findOrFail($this->addressId);
            $address->update([
                'title' => $this->title,
                'province' => $this->province,
                'city' => $this->city,
                'address' => $this->address,
                'postal_code' => $this->postal_code,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'is_default' => $this->is_default,
            ]);
            $msg = 'آدرس با موفقیت ویرایش شد.';
        } else {
            $client->addresses()->create([
                'title' => $this->title,
                'province' => $this->province,
                'city' => $this->city,
                'address' => $this->address,
                'postal_code' => $this->postal_code,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'is_default' => $this->is_default,
            ]);
            $msg = 'آدرس جدید با موفقیت اضافه شد.';
        }

        $this->loadAddresses();
        $this->closeForm();
        $this->dispatch('notify', type: 'success', text: $msg);
    }

    public function editAddress($id)
    {
        $client = auth('client')->user();
        if (!$client) return;

        $address = $client->addresses()->findOrFail($id);
        $this->addressId = $address->id;
        $this->title = $address->title;
        $this->province = $address->province;
        $this->city = $address->city;
        $this->address = $address->address;
        $this->postal_code = $address->postal_code;
        $this->lat = $address->lat ?? 35.6892;
        $this->lng = $address->lng ?? 51.3890;
        $this->is_default = $address->is_default;

        $this->showForm = true;
        
        $this->dispatch('initMap', lat: $this->lat, lng: $this->lng);
    }

    public function deleteAddress($id)
    {
        $client = auth('client')->user();
        if (!$client) return;

        $address = $client->addresses()->findOrFail($id);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            // Set another address as default
            $next = $client->addresses()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        $this->loadAddresses();
        $this->dispatch('notify', type: 'success', text: 'آدرس با موفقیت حذف شد.');
    }

    public function makeDefault($id)
    {
        $client = auth('client')->user();
        if (!$client) return;

        $client->addresses()->update(['is_default' => false]);
        $client->addresses()->findOrFail($id)->update(['is_default' => true]);

        $this->loadAddresses();
        $this->dispatch('notify', type: 'success', text: 'آدرس پیش‌فرض تغییر کرد.');
    }

    public function render()
    {
        return view('clients::livewire.portal.client-address-manager');
    }
}
