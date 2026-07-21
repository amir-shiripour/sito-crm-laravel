<?php

namespace Modules\Market\App\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ProvinceCity;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\App\Helpers\GeolocationHelper;

class LocationModal extends Component
{
    public bool $isOpen = false;
    public bool $mustAddAddress = false;
    public bool $mustSelectLocation = false;

    // Location selection
    public array $provinces = [];
    public array $cities = [];
    public string $selectedProvince = '';
    public string $selectedCity = '';

    // Step configuration
    public int $step = 1; // 1: choose action, 2: map location selector
    public string $searchQuery = '';
    public array $searchResults = [];

    // Quick address creation (logged-in clients)
    public string $newTitle = 'آدرس پیش‌فرض';
    public string $newProvince = '';
    public string $newCity = '';
    public string $newAddress = '';
    public string $newPostalCode = '';
    public float $newLat = 35.6892;
    public float $newLng = 51.3890;

    // Logged in client addresses list
    public array $addresses = [];
    public ?int $selectedAddressId = null;
    public bool $showAddNewAddress = false;

    // Map configuration
    public string $mapProvider = 'neshan';
    public string $mapApiKey = '';

    protected $listeners = [
        'openLocationModal' => 'openModal',
        'updateCoordinates' => 'updateCoordinates'
    ];

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
                $results = $mapService->search($query, $this->newLat, $this->newLng);
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
        $this->newLat = (float)$lat;
        $this->newLng = (float)$lng;
        $this->searchResults = [];
        $this->searchQuery = $title;

        // Fetch address (reverse geocode) to set province/city
        $this->fetchNewAddressFromCoordinates($lat, $lng);

        // Tell Leaflet map to update center and marker position
        $this->dispatch('mapMoveTo', lat: $lat, lng: $lng);
    }

    public function mount()
    {
        $this->provinces = ProvinceCity::getProvinces();
        
        $this->mapProvider = MarketSetting::getValue('map.provider', 'neshan');
        $this->mapApiKey = MarketSetting::getValue('map.api_key', '');

        $this->checkLocationRequirement();
        $this->loadClientAddresses();
    }

    public function loadClientAddresses()
    {
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();
            $this->addresses = $client->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get()->toArray();
            
            $loc = GeolocationHelper::getClientLocation();
            $this->selectedAddressId = null;
            
            // Try to match current active location
            if ($loc) {
                foreach ($this->addresses as $addr) {
                    if ($addr['province'] === ($loc['province'] ?? null) && $addr['city'] === ($loc['city'] ?? null)) {
                        $this->selectedAddressId = $addr['id'];
                        break;
                    }
                }
            }
            
            if (!$this->selectedAddressId && !empty($this->addresses)) {
                $defaultAddr = collect($this->addresses)->firstWhere('is_default', true) ?: $this->addresses[0];
                $this->selectedAddressId = $defaultAddr['id'];
            }
        } else {
            $this->addresses = [];
            $this->selectedAddressId = null;
        }
    }

    public function selectActiveAddress($id)
    {
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();
            $client->addresses()->update(['is_default' => false]);
            
            $address = $client->addresses()->findOrFail($id);
            $address->update(['is_default' => true]);

            session(['client_location' => [
                'province' => $address->province,
                'city' => $address->city
            ]]);

            $this->isOpen = false;
            $this->dispatch('notify', type: 'success', text: 'موقعیت فعال شما تغییر یافت.');
            $this->dispatch('locationUpdated');
            return redirect(request()->header('Referer') ?: '/shop');
        }
    }

    public function toggleAddNewAddress(bool $show)
    {
        $this->showAddNewAddress = $show;
        if ($show) {
            $this->newTitle = 'آدرس جدید';
            $this->newProvince = '';
            $this->newCity = '';
            $this->newAddress = '';
            $this->newPostalCode = '';
            $this->newLat = 35.6892;
            $this->newLng = 51.3890;
            // dispatch map initialization event
            $this->dispatch('initMap', lat: $this->newLat, lng: $this->newLng);
        }
    }

    public function checkLocationRequirement()
    {
        if (!MarketSetting::getValue('orders.enable_geolocation_ordering', false)) {
            return;
        }

        $loc = GeolocationHelper::getClientLocation();
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();
            if ($client->addresses()->count() === 0) {
                $this->mustAddAddress = true;
                $this->isOpen = true;
            } else {
                $this->mustAddAddress = false;
                if ($loc) {
                    $this->selectedProvince = $loc['province'] ?? '';
                    $this->selectedCity = $loc['city'] ?? '';
                }
            }
        } else {
            if (!$loc || empty($loc['city'])) {
                $this->mustSelectLocation = true;
                $this->isOpen = true;
            } else {
                $this->mustSelectLocation = false;
                $this->selectedProvince = $loc['province'] ?? '';
                $this->selectedCity = $loc['city'] ?? '';
            }
        }

        if ($this->selectedProvince) {
            $this->cities = ProvinceCity::getCities($this->selectedProvince);
        }
    }

    public function updatedSelectedProvince($value)
    {
        $this->cities = ProvinceCity::getCities($value);
        $this->selectedCity = '';
    }

    public function updatedNewProvince($value)
    {
        $this->cities = ProvinceCity::getCities($value);
        $this->newCity = '';
    }

    public function confirmGuestLocation()
    {
        $this->validate([
            'selectedProvince' => 'required|string',
            'selectedCity' => 'required|string',
        ], [
            'selectedProvince.required' => 'استان را انتخاب کنید.',
            'selectedCity.required' => 'شهر را انتخاب کنید.',
        ]);

        session(['client_location' => [
            'province' => $this->selectedProvince,
            'city' => $this->selectedCity
        ]]);

        $this->isOpen = false;
        $this->mustSelectLocation = false;
        
        $this->dispatch('notify', type: 'success', text: 'موقعیت مکانی شما با موفقیت ثبت شد.');
        $this->dispatch('locationUpdated');
        
        // Reload page to apply products city filtering
        return redirect(request()->header('Referer') ?: '/shop');
    }

    public function updateCoordinates($lat, $lng)
    {
        $this->newLat = (float)$lat;
        $this->newLng = (float)$lng;
    }

    public function saveClientAddress()
    {
        $this->validate([
            'newTitle' => 'required|string|max:100',
            'newProvince' => 'required|string',
            'newCity' => 'required|string',
            'newAddress' => 'required|string|min:5',
            'newPostalCode' => 'nullable|string|size:10',
        ], [
            'newTitle.required' => 'عنوان آدرس الزامی است.',
            'newProvince.required' => 'استان الزامی است.',
            'newCity.required' => 'شهر الزامی است.',
            'newAddress.required' => 'آدرس دقیق الزامی است.',
            'newAddress.min' => 'آدرس دقیق باید حداقل ۵ کاراکتر باشد.',
            'newPostalCode.size' => 'کد پستی باید دقیقاً ۱۰ رقم باشد.',
        ]);

        $client = Auth::guard('client')->user();
        if (!$client) {
            return;
        }

        // De-default other addresses
        $client->addresses()->update(['is_default' => false]);

        $address = $client->addresses()->create([
            'title' => $this->newTitle,
            'province' => $this->newProvince,
            'city' => $this->newCity,
            'address' => $this->newAddress,
            'postal_code' => $this->newPostalCode,
            'lat' => $this->newLat,
            'lng' => $this->newLng,
            'is_default' => true,
        ]);

        session(['client_location' => [
            'province' => $address->province,
            'city' => $address->city
        ]]);

        $this->isOpen = false;
        $this->mustAddAddress = false;
        $this->showAddNewAddress = false;

        $this->dispatch('notify', type: 'success', text: 'آدرس شما با موفقیت ثبت و به عنوان موقعیت پیش‌فرض انتخاب شد.');
        return redirect(request()->header('Referer') ?: '/shop');
    }

    public function skipLocationSelection()
    {
        $this->isOpen = false;
        $this->mustSelectLocation = false;
    }

    public function fetchNewAddressFromCoordinates($lat, $lng)
    {
        $this->newLat = (float)$lat;
        $this->newLng = (float)$lng;
        
        $geoData = [];
        if (interface_exists(\Modules\Market\App\Services\Map\MapServiceInterface::class) && app()->bound(\Modules\Market\App\Services\Map\MapServiceInterface::class)) {
            $mapService = app(\Modules\Market\App\Services\Map\MapServiceInterface::class);
            $geoData = $mapService->reverseGeocode($lat, $lng);
        }

        if (empty($geoData['address'])) {
            $geoData = $this->fallbackGeocode($lat, $lng);
        }

        if (!empty($geoData['address'])) {
            $rawProvince = $geoData['province'] ?? ($this->mustAddAddress || $this->showAddNewAddress || Auth::guard('client')->check() ? $this->newProvince : $this->selectedProvince);
            $rawCity = $geoData['city'] ?? ($this->mustAddAddress || $this->showAddNewAddress || Auth::guard('client')->check() ? $this->newCity : $this->selectedCity);

            // Normalize Province
            $rawProvince = trim(str_replace(['استان ', ' استان', ' Province'], '', $rawProvince));

            // Normalize City
            $rawCity = trim(str_replace(['شهر ', ' شهر', ' City', 'شهرستان ', ' بخش '], '', $rawCity));

            if ($this->mustAddAddress || $this->showAddNewAddress || Auth::guard('client')->check()) {
                $this->newProvince = $rawProvince;
                $this->newCity = $rawCity;
                $this->newAddress = $this->sanitizeAddress($geoData['address'], $rawProvince, $rawCity);
                if (isset($geoData['data']['postal_code']) && !empty($geoData['data']['postal_code'])) {
                    $this->newPostalCode = $geoData['data']['postal_code'];
                } elseif (isset($geoData['postal_code']) && !empty($geoData['postal_code'])) {
                    $this->newPostalCode = $geoData['postal_code'];
                }
            } else {
                $this->selectedProvince = $rawProvince;
                $this->selectedCity = $rawCity;
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
        } catch (\Exception $e) {}
        return [];
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->step = 1;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->showAddNewAddress = false;
        $this->checkLocationRequirement();
        $this->loadClientAddresses();
    }

    public function render()
    {
        $enabled = MarketSetting::getValue('orders.enable_geolocation_ordering', false);
        return view('market::livewire.web.location-modal', [
            'enabled' => $enabled
        ]);
    }
}
