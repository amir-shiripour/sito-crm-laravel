<?php

namespace Modules\Market\App\Livewire\Vendor;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\VendorAddress;
use Modules\Market\Entities\VendorDocument;
use Illuminate\Support\Str;

class KycWizard extends Component
{
    use WithFileUploads;

    public $currentStep = 1;
    public ?Vendor $vendor = null;

    // مرحله ۱: اطلاعات پایه
    public $store_name = '';
    public $slug = '';
    public $support_phone = '';
    public $legal_type = 'real';
    public $national_code = '';
    public $economic_code = '';

    // مرحله ۲: مالی
    public $shaba_number = '';
    public $account_owner_name = '';
    public $bank_name = '';

    // مرحله ۳: آدرس
    public $province = '';
    public $city = '';
    public $address = '';
    public $postal_code = '';

    // مرحله ۴: مدارک (آپلود)
    public $nationalCardFile;
    public $businessLicenseFile;
    public $existingNationalCard = null;
    public $existingBusinessLicense = null;
    public $kyc_rejection_reason = '';

    public function mount()
    {
        $this->vendor = auth()->user()->marketVendor;

        if ($this->vendor) {
            // پر کردن اطلاعات قبلی در صورت وجود
            $this->store_name = $this->vendor->store_name;
            $this->slug = $this->vendor->slug;
            $this->support_phone = $this->vendor->support_phone;
            $this->legal_type = $this->vendor->legal_type ?? 'real';
            $this->national_code = $this->vendor->national_code;
            $this->economic_code = $this->vendor->economic_code ?? '';
            $this->shaba_number = $this->vendor->shaba_number;
            $this->account_owner_name = $this->vendor->account_owner_name;
            $this->bank_name = $this->vendor->bank_name;

            // گرفتن آدرس پیش‌فرض اگه داره
            $addr = $this->vendor->addresses()->where('is_default', true)->first();
            if ($addr) {
                $this->province = $addr->province;
                $this->city = $addr->city;
                $this->address = $addr->address;
                $this->postal_code = $addr->postal_code;
            }
            $this->kyc_rejection_reason = $this->vendor->kyc_rejection_reason;
            $this->existingNationalCard = $this->vendor->documents()->where('type', 'national_card')->first();
            $this->existingBusinessLicense = $this->vendor->documents()->where('type', 'business_license')->first();
        }
    }

    public function nextStep()
    {
        // اعتبارسنجی هر مرحله قبل از رفتن به مرحله بعد
        if ($this->currentStep === 1) {
            $rules = [
                'store_name' => 'required|string|max:255',
                'support_phone' => 'required|string',
                'legal_type' => 'required|in:real,legal',
                'national_code' => 'required|string',
            ];

            // اگر شخص حقوقی بود، کد اقتصادی الزامی بشه
            if ($this->legal_type === 'legal') {
                $rules['economic_code'] = 'required|string';
            }
            $this->validate($rules);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'shaba_number' => 'required|string|size:24',
                'account_owner_name' => 'required|string',
            ]);
        } elseif ($this->currentStep === 3) {
            $this->validate([
                'province' => 'required|string',
                'city' => 'required|string',
                'address' => 'required|string',
            ]);
        }

        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    public function submit()
    {
        $rules = [];

        // بررسی ولیدیشن کارت ملی
        if (!$this->existingNationalCard || $this->existingNationalCard->status === 'rejected') {
            // اگر مدرک نداره یا رد شده، آپلود الزامیه
            $rules['nationalCardFile'] = 'required|image|max:2048';
        } elseif ($this->nationalCardFile) {
            // اگر تایید شده ولی خودش یک فایل جدید انتخاب کرده، فقط فرمت و حجمش چک بشه
            $rules['nationalCardFile'] = 'image|max:2048';
        }

        // بررسی ولیدیشن جواز کسب (فقط برای حقوقی‌ها)
        if ($this->legal_type === 'legal') {
            if (!$this->existingBusinessLicense || $this->existingBusinessLicense->status === 'rejected') {
                $rules['businessLicenseFile'] = 'required|image|max:2048';
            } elseif ($this->businessLicenseFile) {
                $rules['businessLicenseFile'] = 'image|max:2048';
            }
        }

        // 💡 حل مشکل Livewire: ولیدیت رو فقط زمانی صدا می‌زنیم که رولی برای چک کردن وجود داشته باشه
        if (!empty($rules)) {
            $this->validate($rules);
        }

        $user = auth()->user();

        // ۱. ذخیره یا آپدیت فروشگاه
        if (!$this->vendor) {
            $this->vendor = new Vendor(['user_id' => $user->id]);
        }

        $this->vendor->fill([
            'store_name' => $this->store_name,
            'slug' => $this->slug ?: Str::slug($this->store_name),
            'support_phone' => $this->support_phone,
            'legal_type' => $this->legal_type,
            'national_code' => $this->national_code,
            'economic_code' => $this->economic_code,
            'shaba_number' => $this->shaba_number,
            'account_owner_name' => $this->account_owner_name,
            'bank_name' => $this->bank_name,
            'kyc_status' => 'pending', // تغییر وضعیت به در حال بررسی
            'status' => 'pending',
        ])->save();

        // ۲. ذخیره آدرس
        VendorAddress::updateOrCreate(
            ['vendor_id' => $this->vendor->id, 'type' => 'store'],
            [
                'province' => $this->province,
                'city' => $this->city,
                'address' => $this->address,
                'postal_code' => $this->postal_code,
                'is_default' => true
            ]
        );

        // ۳. ذخیره مدارک (فقط در صورتی که فایل جدیدی آپلود شده باشه)
        if ($this->nationalCardFile) {
            $path = $this->nationalCardFile->store('vendor-documents', 'public');
            VendorDocument::updateOrCreate(
                ['vendor_id' => $this->vendor->id, 'type' => 'national_card'],
                ['file_path' => $path, 'status' => 'pending', 'rejection_reason' => null]
            );
        }

        if ($this->businessLicenseFile) {
            $path = $this->businessLicenseFile->store('vendor-documents', 'public');
            VendorDocument::updateOrCreate(
                ['vendor_id' => $this->vendor->id, 'type' => 'business_license'],
                ['file_path' => $path, 'status' => 'pending', 'rejection_reason' => null]
            );
        }

        // دادن نقش فروشنده
        if (!$user->hasRole('vendor')) {
            $user->assignRole('vendor');
        }

        $this->dispatch('notify', type: 'success', text: 'اطلاعات شما با موفقیت ثبت شد و در انتظار تایید مدیریت است.');

        return redirect()->route('user.market.dashboard');
    }

    public function render()
    {
        return view('market::livewire.vendor.kyc-wizard');
    }
}
