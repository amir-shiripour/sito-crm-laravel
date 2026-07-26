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

        if (interface_exists(\Modules\Market\App\Services\Map\MapServiceInterface::class) && app()->bound(\Modules\Market\App\Services\Map\MapServiceInterface::class)) {
            $mapService = app(\Modules\Market\App\Services\Map\MapServiceInterface::class);
            $this->searchResults = $mapService->search($query, $this->lat, $this->lng);
        } else {
            $this->searchResults = [];
        }
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
            $this->province = $geoData['province'] ?? $this->province;
            $this->city = $geoData['city'] ?? $this->city;
            
            // Sanitize address to make it Snapp-like
            $this->address = $this->sanitizeAddress($geoData['address'], $this->province, $this->city);
            
            if (isset($geoData['data']['postal_code']) && !empty($geoData['data']['postal_code'])) {
                $this->postal_code = $geoData['data']['postal_code'];
            } elseif (isset($geoData['postal_code']) && !empty($geoData['postal_code'])) {
                $this->postal_code = $geoData['postal_code'];
            }
        }
    }

    protected function sanitizeAddress($address, $province, $city)
    {
        if (empty($address)) {
            return '';
        }

        // Remove country
        $address = preg_replace('/^(ایران|iran)\s*[،,]\s*/iu', '', $address);

        // Remove province variations from the beginning
        if (!empty($province)) {
            $provinceClean = str_replace(['استان ', ' Province'], '', $province);
            $address = preg_replace('/^(' . preg_quote($province, '/') . '|' . preg_quote($provinceClean, '/') . '|استان\s+' . preg_quote($provinceClean, '/') . ')\s*[،,]\s*/iu', '', $address);
        }

        // Remove city variations from the beginning
        if (!empty($city)) {
            $cityClean = str_replace(['شهر ', ' City'], '', $city);
            $address = preg_replace('/^(' . preg_quote($city, '/') . '|' . preg_quote($cityClean, '/') . '|شهر\s+' . preg_quote($cityClean, '/') . ')\s*[،,]\s*/iu', '', $address);
        }

        // Remove postal code from address string if present
        $address = preg_replace('/\b\d{5}-?\d{5}\b/u', '', $address);

        // Multibyte-safe trim for spaces and commas
        $address = preg_replace('/^[،,\s]+|[،,\s]+$/u', '', $address);
        
        // Remove trailing country/province/city if they are appended at the end (for OSM display name)
        if (!empty($province)) {
            $provinceClean = str_replace(['استان ', ' Province'], '', $province);
            $address = preg_replace('/\s*[،,]\s*(' . preg_quote($province, '/') . '|' . preg_quote($provinceClean, '/') . '|استان\s+' . preg_quote($provinceClean, '/') . ')$/iu', '', $address);
        }
        if (!empty($city)) {
            $cityClean = str_replace(['شهر ', ' City'], '', $city);
            $address = preg_replace('/\s*[،,]\s*(' . preg_quote($city, '/') . '|' . preg_quote($cityClean, '/') . '|شهر\s+' . preg_quote($cityClean, '/') . ')$/iu', '', $address);
        }
        $address = preg_replace('/\s*[،,]\s*(ایران|iran)$/iu', '', $address);

        return preg_replace('/^[،,\s]+|[،,\s]+$/u', '', $address);
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
                $province = str_replace(['استان ', ' Province'], '', $province);

                $city = $addressData['city'] ?? $addressData['town'] ?? $addressData['suburb'] ?? $addressData['village'] ?? '';

                // Build a clean Snapp-like address from OSM structured components
                $addressParts = [];
                if (!empty($addressData['road'])) {
                    $addressParts[] = $addressData['road'];
                }
                if (!empty($addressData['neighbourhood'])) {
                    $addressParts[] = $addressData['neighbourhood'];
                }
                if (!empty($addressData['suburb']) && $addressData['suburb'] !== $city) {
                    $addressParts[] = $addressData['suburb'];
                }
                if (!empty($addressData['borough'])) {
                    $addressParts[] = $addressData['borough'];
                }

                // Fallback to cleaned display_name if parts are too empty
                if (count($addressParts) < 2) {
                    $formattedAddress = $data['display_name'] ?? '';
                } else {
                    $formattedAddress = implode('، ', $addressParts);
                }

                // Extract postcode if available
                $postcode = $addressData['postcode'] ?? '';
                if (!empty($postcode)) {
                    $postcode = str_replace('-', '', $postcode);
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
