<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\VendorDocument;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class VendorForm extends Component
{
    public ?Vendor $vendor = null;

    // اطلاعات پایه
    public $user_id = '';
    public $store_name = '';
    public $slug = '';
    public $support_phone = '';
    public $description = '';
    public $status = 'pending';
    public $kyc_status = 'pending';
    public $commission_rate = '';

    // اطلاعات حقوقی (KYC)
    public $legal_type = 'real';
    public $national_code = '';
    public $economic_code = '';

    // اطلاعات مالی
    public $shaba_number = '';
    public $account_owner_name = '';
    public $bank_name = '';

    // متغیرهای مودال رد مدرک
    public $rejectingDocId = null;
    public $rejectionReason = '';

    public $kyc_rejection_reason = '';

    // Geolocation & Address management for admin
    public bool $showAddressModal = false;
    public $editingAddressId = null;
    public string $addrType = 'warehouse';
    public string $addrProvince = '';
    public string $addrCity = '';
    public string $addrAddress = '';
    public string $addrPostalCode = '';
    public float $addrLat = 35.6892;
    public float $addrLng = 51.3890;
    public string $mapProvider = 'neshan';
    public string $mapApiKey = '';

    // Search fields for location
    public string $searchQuery = '';
    public array $searchResults = [];

    protected $listeners = [
        'updateAddrCoordinates' => 'updateAddrCoordinates'
    ];

    public function mount(?Vendor $vendor = null)
    {
        $this->vendor = $vendor ?? new Vendor();

        if (class_exists(\Modules\Market\Entities\MarketSetting::class)) {
            $this->mapProvider = \Modules\Market\Entities\MarketSetting::getValue('map.provider', 'neshan');
            $this->mapApiKey = \Modules\Market\Entities\MarketSetting::getValue('map.api_key', '');
        }

        if ($this->vendor->exists) {
            $this->user_id = $this->vendor->user_id;
            $this->store_name = $this->vendor->store_name;
            $this->slug = $this->vendor->slug;
            $this->support_phone = $this->vendor->support_phone;
            $this->description = $this->vendor->description;
            $this->status = $this->vendor->status;
            $this->kyc_status = $this->vendor->kyc_status;
            $this->commission_rate = $this->vendor->commission_rate;

            $this->legal_type = $this->vendor->legal_type ?? 'real';
            $this->national_code = $this->vendor->national_code;
            $this->economic_code = $this->vendor->economic_code;

            $this->shaba_number = $this->vendor->shaba_number;
            $this->account_owner_name = $this->vendor->account_owner_name;
            $this->bank_name = $this->vendor->bank_name;
            $this->kyc_rejection_reason = $this->vendor->kyc_rejection_reason;
        }
    }

    public function addAddress()
    {
        $this->editingAddressId = null;
        $this->addrType = 'warehouse';
        $this->addrProvince = '';
        $this->addrCity = '';
        $this->addrAddress = '';
        $this->addrPostalCode = '';
        $this->addrLat = 35.6892;
        $this->addrLng = 51.3890;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->showAddressModal = true;
    }

    public function editAddress($addressId)
    {
        $address = $this->vendor->addresses()->findOrFail($addressId);
        $this->editingAddressId = $address->id;
        $this->addrType = $address->type;
        $this->addrProvince = $address->province;
        $this->addrCity = $address->city;
        $this->addrAddress = $address->address;
        $this->addrPostalCode = $address->postal_code ?? '';
        $this->addrLat = $address->latitude ?? 35.6892;
        $this->addrLng = $address->longitude ?? 51.3890;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->showAddressModal = true;
    }

    public function saveAddress()
    {
        $this->validate([
            'addrType' => 'required|in:warehouse,office,store',
            'addrProvince' => 'required|string',
            'addrCity' => 'required|string',
            'addrAddress' => 'required|string|min:5',
            'addrPostalCode' => 'nullable|string|size:10',
        ], [
            'addrType.required' => 'نوع آدرس الزامی است.',
            'addrProvince.required' => 'استان الزامی است.',
            'addrCity.required' => 'شهر الزامی است.',
            'addrAddress.required' => 'آدرس دقیق الزامی است.',
            'addrAddress.min' => 'آدرس دقیق باید حداقل ۵ کاراکتر باشد.',
            'addrPostalCode.size' => 'کد پستی باید ۱۰ رقم باشد.',
        ]);

        $isDefault = $this->vendor->addresses()->count() === 0;

        $this->vendor->addresses()->updateOrCreate(
            ['id' => $this->editingAddressId],
            [
                'type' => $this->addrType,
                'province' => $this->addrProvince,
                'city' => $this->addrCity,
                'address' => $this->addrAddress,
                'postal_code' => $this->addrPostalCode ?: null,
                'latitude' => $this->addrLat,
                'longitude' => $this->addrLng,
                'is_default' => $this->editingAddressId ? (bool)$this->vendor->addresses()->find($this->editingAddressId)->is_default : $isDefault
            ]
        );

        $this->showAddressModal = false;
        $this->dispatch('notify', type: 'success', text: 'آدرس با موفقیت ذخیره شد.');
        $this->vendor->load('addresses'); // Refresh relation
    }

    public function deleteAddress($addressId)
    {
        $address = $this->vendor->addresses()->findOrFail($addressId);
        $address->delete();
        $this->dispatch('notify', type: 'success', text: 'آدرس با موفقیت حذف شد.');
        $this->vendor->load('addresses'); // Refresh relation
    }

    public function updateAddrCoordinates($lat, $lng)
    {
        $this->addrLat = (float)$lat;
        $this->addrLng = (float)$lng;
    }

    public function fetchAddrCoordinatesAddress($lat, $lng)
    {
        $this->addrLat = $lat;
        $this->addrLng = $lng;
        $geoData = [];

        if (interface_exists(\Modules\Market\App\Services\Map\MapServiceInterface::class) && app()->bound(\Modules\Market\App\Services\Map\MapServiceInterface::class)) {
            $mapService = app(\Modules\Market\App\Services\Map\MapServiceInterface::class);
            $geoData = $mapService->reverseGeocode($lat, $lng);
        }

        if (empty($geoData['address'])) {
            $geoData = $this->fallbackGeocode($lat, $lng);
        }

        if (!empty($geoData['address'])) {
            $this->addrProvince = $geoData['province'] ?? $this->addrProvince;
            $this->addrCity = $geoData['city'] ?? $this->addrCity;
            $this->addrAddress = $this->sanitizeAddress($geoData['address'], $this->addrProvince, $this->addrCity);
            
            if (isset($geoData['data']['postal_code']) && !empty($geoData['data']['postal_code'])) {
                $this->addrPostalCode = $geoData['data']['postal_code'];
            } elseif (isset($geoData['postal_code']) && !empty($geoData['postal_code'])) {
                $this->addrPostalCode = $geoData['postal_code'];
            }
        }
    }

    protected function sanitizeAddress($address, $province, $city)
    {
        if (empty($address)) return '';
        $address = preg_replace('/^(ایران|iran)\s*[،,]\s*/iu', '', $address);

        if (!empty($province)) {
            $provinceClean = str_replace(['استان ', ' Province'], '', $province);
            $address = preg_replace('/^(' . preg_quote($province, '/') . '|' . preg_quote($provinceClean, '/') . '|استان\s+' . preg_quote($provinceClean, '/') . ')\s*[،,]\s*/iu', '', $address);
        }

        if (!empty($city)) {
            $cityClean = str_replace(['شهر ', ' City'], '', $city);
            $address = preg_replace('/^(' . preg_quote($city, '/') . '|' . preg_quote($cityClean, '/') . '|شهر\s+' . preg_quote($cityClean, '/') . ')\s*[،,]\s*/iu', '', $address);
        }

        $address = preg_replace('/\b\d{5}-?\d{5}\b/u', '', $address);
        $address = preg_replace('/^[،,\s]+|[،,\s]+$/u', '', $address);
        
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

                $addressStr = implode('، ', $addressParts);
                return [
                    'province' => $province,
                    'city' => $city,
                    'address' => $addressStr,
                    'postal_code' => $addressData['postcode'] ?? null
                ];
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        return [];
    }

    public function save()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id|unique:market_vendors,user_id,' . ($this->vendor->id ?? 'NULL'),
            'store_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:market_vendors,slug,' . ($this->vendor->id ?? 'NULL'),
            'status' => 'required|in:pending,active,suspended',
            'kyc_status' => 'required|in:pending,approved,rejected',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'legal_type' => 'required|in:real,legal',
            'national_code' => 'nullable|string|max:20',
            'shaba_number' => 'nullable|string|size:24', // IR حذف شده و فقط ۲۴ رقم شبا
        ]);

        $this->vendor->fill([
            'user_id' => $this->user_id,
            'store_name' => $this->store_name,
            'slug' => $this->slug ?: Str::slug($this->store_name),
            'support_phone' => $this->support_phone,
            'description' => $this->description,
            'status' => $this->status,
            'kyc_status' => $this->kyc_status,
            'commission_rate' => $this->commission_rate ?: null,
            'legal_type' => $this->legal_type,
            'national_code' => $this->national_code,
            'economic_code' => $this->economic_code,
            'shaba_number' => $this->shaba_number,
            'account_owner_name' => $this->account_owner_name,
            'bank_name' => $this->bank_name,
            'kyc_rejection_reason' => $this->kyc_status === 'rejected' ? $this->kyc_rejection_reason : null,
        ])->save();

        // اختصاص نقش به کاربر
        $user = User::find($this->user_id);
        if ($user && !$user->hasRole('vendor')) {
            $user->assignRole('vendor');
        }

        $this->dispatch('notify', type: 'success', text: 'اطلاعات فروشگاه با موفقیت ذخیره شد.');
        \Illuminate\Support\Facades\Cache::forget('vendor_edit_lock_' . $this->vendor->id);
        return redirect()->route('user.market.vendors.index');
    }

    // --- متدهای بررسی مدارک ---

    public function approveDocument($docId)
    {
        $doc = VendorDocument::find($docId);
        if ($doc && $doc->vendor_id === $this->vendor->id) {
            $doc->update(['status' => 'approved', 'rejection_reason' => null]);
            $this->dispatch('notify', type: 'success', text: 'مدرک تایید شد.');
        }
    }

    public function promptRejectDocument($docId)
    {
        $this->rejectingDocId = $docId;
        $this->rejectionReason = '';
    }

    public function confirmRejectDocument()
    {
        $this->validate(['rejectionReason' => 'required|string|min:5']);

        $doc = VendorDocument::find($this->rejectingDocId);
        if ($doc && $doc->vendor_id === $this->vendor->id) {
            $doc->update(['status' => 'rejected', 'rejection_reason' => $this->rejectionReason]);
            $this->dispatch('notify', type: 'success', text: 'مدرک رد شد و دلیل آن برای فروشنده ثبت گردید.');
        }

        $this->rejectingDocId = null;
        $this->rejectionReason = '';
    }

    public function cancelReject()
    {
        $this->rejectingDocId = null;
    }

    public function render()
    {
        return view('market::livewire.admin.vendor-form', [
            'users' => User::select('id', 'name', 'mobile')->latest()->get(),
            // لود کردن آدرس‌ها و مدارک فقط در صورت ویرایش
            'addresses' => $this->vendor->exists ? $this->vendor->addresses : collect(),
            'documents' => $this->vendor->exists ? $this->vendor->documents : collect(),
        ]);
    }

    public function updatedSearchQuery($query)
    {
        if (strlen($query) < 3) {
            $this->searchResults = [];
            return;
        }

        if (interface_exists(\Modules\Market\App\Services\Map\MapServiceInterface::class) && app()->bound(\Modules\Market\App\Services\Map\MapServiceInterface::class)) {
            $mapService = app(\Modules\Market\App\Services\Map\MapServiceInterface::class);
            $this->searchResults = $mapService->search($query, $this->addrLat, $this->addrLng);
        } else {
            $this->searchResults = [];
        }
    }

    public function selectSearchResult($lat, $lng, $title = '')
    {
        $this->addrLat = (float)$lat;
        $this->addrLng = (float)$lng;
        $this->searchResults = [];
        $this->searchQuery = $title;

        $this->fetchAddrCoordinatesAddress($lat, $lng);

        $this->dispatch('mapMoveTo', lat: $lat, lng: $lng);
    }

    public function cancelReview()
    {
        if ($this->vendor->exists) {
            Cache::forget('vendor_edit_lock_' . $this->vendor->id);
        }
        return redirect()->route('user.market.vendors.index');
    }
}
