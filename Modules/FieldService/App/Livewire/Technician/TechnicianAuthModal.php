<?php

namespace Modules\FieldService\App\Livewire\Technician;

use App\Helpers\ProvinceCity;
use App\Models\User;
use App\Services\RegistrationOtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\FieldServiceSetting;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\TechnicianLifecycleService;
use Modules\Sms\Entities\SmsOtp;
use Spatie\Permission\Models\Role;

class TechnicianAuthModal extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;

    // Step state: 1 = Phone Input & Detection, 2 = OTP Verification, 3 = Register Step 1 (Personal & Location), 4 = Register Step 2 (Skills & Documents)
    public int $step = 1;

    // Flow state: 'login' | 'register'
    public string $authFlow = 'login';
    public ?string $recognizedName = null;

    // Step 1: Phone
    public string $phone = '';

    // Step 2: OTP
    public string $otpCode = '';
    public bool $otpSent = false;
    public int $otpResendIn = 60;
    public ?string $devOtpCode = null;
    public bool $isPhoneVerified = false;

    // Step 3: Registration details (harmonized with /register and technician custom fields)
    public string $firstName = '';
    public string $lastName = '';
    public string $nationalCode = '';
    public string $province = 'مازندران';
    public string $city = 'بابل';
    public string $address = '';
    public string $shabaNumber = '';
    public $nationalCardImage = null;
    public array $selectedCategories = [];

    // Alerts
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    protected $listeners = [
        'open-technician-auth'  => 'openModal',
        'close-technician-auth' => 'closeModal',
        'logout-technician'     => 'logoutTechnician',
    ];

    public function mount()
    {
        $user = Auth::user();

        // If user is already authenticated with technician role and active record
        if ($user && $user->hasRole('technician')) {
            $tech = Technician::where('user_id', $user->id)->first();
            if ($tech) {
                $this->isOpen = false;
                return;
            }
        }

        // Open modal by default for unauthenticated technician visitors
        $this->isOpen = true;
        $this->initDefaultLocation();
    }

    public function initDefaultLocation()
    {
        $this->province = 'مازندران';
        $cities = ProvinceCity::getCities('مازندران');
        $this->city = in_array('بابل', $cities, true) ? 'بابل' : ($cities[0] ?? 'بابل');
    }

    public function updatedProvince($value)
    {
        $cities = ProvinceCity::getCities($value);
        if ($value === 'مازندران' && in_array('بابل', $cities, true)) {
            $this->city = 'بابل';
        } else {
            $this->city = $cities[0] ?? '';
        }
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->clearMessages();
    }

    public function closeModal()
    {
        $user = Auth::user();
        if ($user && $user->hasRole('technician') && Technician::where('user_id', $user->id)->exists()) {
            $this->isOpen = false;
            $this->clearMessages();
        }
    }

    public function clearMessages()
    {
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public static function normalizePhoneNumber(?string $number): string
    {
        return RegistrationOtpService::normalizePhoneNumber($number);
    }

    // =========================================================================
    // STEP 1: PHONE INPUT & SMART USER DETECTION
    // =========================================================================

    public function submitPhone()
    {
        $this->clearMessages();
        $normalized = self::normalizePhoneNumber($this->phone);

        if (empty($normalized) || strlen($normalized) !== 11 || !str_starts_with($normalized, '09')) {
            $this->errorMessage = 'لطفاً یک شماره همراه معتبر ۱۱ رقمی (مثال: 09112223344) وارد فرمایید.';
            return;
        }

        $this->phone = $normalized;

        // Smart Detection: An applicant is ONLY considered an existing login if an actual User account exists
        // AND that user has the 'technician' role OR is linked to an active Technician record with matching user_id.
        $user = User::where('mobile', $normalized)->first();
        $tech = Technician::where('mobile', $normalized)->first();

        if ($user && ($user->hasRole('technician') || ($tech && $tech->user_id === $user->id))) {
            $this->authFlow = 'login';
            $this->recognizedName = ($tech && $tech->user_id === $user->id) ? $tech->full_name : $user->name;
        } else {
            $this->authFlow = 'register';
            $this->recognizedName = null;
        }

        // Send OTP
        $this->sendOtpRequest();
    }

    public function sendOtpRequest()
    {
        $res = RegistrationOtpService::sendOtp($this->phone);

        if (!$res['success']) {
            $this->errorMessage = $res['message'] ?? 'خطا در ارسال کد تایید پیامکی.';
            if (!empty($res['resend_in'])) {
                $this->otpResendIn = (int) $res['resend_in'];
            }
            return;
        }

        $this->otpSent = true;
        $this->otpResendIn = (int) ($res['resend_in'] ?? 60);
        $this->step = 2;

        // Developer testing convenience on local / debug
        if (config('app.debug') || app()->environment('local', 'testing')) {
            $latestOtp = SmsOtp::where('phone', $this->phone)->where('context', RegistrationOtpService::CONTEXT)->latest()->first();
            if ($latestOtp) {
                $this->devOtpCode = $latestOtp->code;
                $prefix = $this->authFlow === 'login' ? 'کد تایید ورود' : 'کد تایید ثبت‌نام';
                $this->successMessage = "{$prefix} ارسال شد. (کد تستی: {$latestOtp->code})";
                return;
            }
        }

        $this->successMessage = $this->authFlow === 'login'
            ? "کد تایید ورود به شماره {$this->phone} پیامک شد."
            : "کد تایید احراز شماره جهت عضویت در ناوگان به {$this->phone} ارسال گردید.";
    }

    public function changePhone()
    {
        $this->step = 1;
        $this->otpSent = false;
        $this->otpCode = '';
        $this->devOtpCode = null;
        $this->isPhoneVerified = false;
        $this->clearMessages();
    }

    public function resendOtp()
    {
        $this->clearMessages();
        $this->sendOtpRequest();
    }

    // =========================================================================
    // STEP 2: VERIFY OTP CODE
    // =========================================================================

    public function verifyOtp()
    {
        $this->clearMessages();
        $code = trim(self::normalizePhoneNumber($this->otpCode));

        if (empty($code) || strlen($code) < 4) {
            $this->errorMessage = 'لطفاً کد تایید پیامکی را به طور کامل وارد کنید.';
            return;
        }

        $verify = RegistrationOtpService::verifyOtp($this->phone, $code);
        if (!$verify['success']) {
            $this->errorMessage = $verify['message'] ?? 'کد تایید وارد شده نادرست یا منقضی شده است.';
            return;
        }

        $this->isPhoneVerified = true;

        if ($this->authFlow === 'login') {
            // Existing technician login
            $user = User::where('mobile', $this->phone)->first();
            $tech = Technician::where('mobile', $this->phone)->first();

            // Guard: If user does not exist in `users` table, force transition to registration
            if (!$user) {
                $this->authFlow = 'register';
                $this->step = 3;
                $this->successMessage = 'شماره همراه شما با موفقیت تایید شد. لطفاً مشخصات فردی و محل فعالیت را وارد فرمایید.';
                return;
            }

            // Ensure Spatie role
            $role = Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web'], [
                'display_name' => 'متخصص و تکنسین فنی',
            ]);
            if (!$user->hasRole('technician')) {
                $user->assignRole($role);
            }

            // Ensure Technician record linked
            if ($tech) {
                if ($tech->user_id !== $user->id) {
                    $tech->user_id = $user->id;
                    $tech->save();
                }
            } else {
                $tech = TechnicianLifecycleService::createOrUpdateTechnicianForUser($user, [
                    'mobile' => $this->phone,
                ]);
            }

            Auth::login($user, true);
            if (request()->hasSession()) {
                request()->session()->regenerate();
            }

            $this->isOpen = false;
            return $this->redirect(route('fieldservice.technician'), navigate: false);
        }

        // New applicant registration flow: Proceed to Step 3 (Personal & Location Info)
        $this->step = 3;
        $this->successMessage = 'شماره همراه شما با موفقیت تایید شد. لطفاً مشخصات فردی و محل فعالیت را وارد فرمایید.';
    }

    // =========================================================================
    // STEP 3: PROCEED TO SKILLS & DOCUMENTS (STEP 4)
    // =========================================================================

    public function goToStep4()
    {
        $this->clearMessages();

        $this->validate([
            'firstName'    => 'required|string|max:100',
            'lastName'     => 'required|string|max:100',
            'nationalCode' => 'required|string|size:10',
            'province'     => 'required|string',
            'city'         => 'required|string',
            'address'      => 'nullable|string|max:500',
            'shabaNumber'  => 'nullable|string|max:34',
        ], [
            'firstName.required'    => 'وارد کردن نام الزامی است.',
            'lastName.required'     => 'وارد کردن نام خانوادگی الزامی است.',
            'nationalCode.required' => 'وارد کردن کد ملی ۱۰ رقمی الزامی است.',
            'nationalCode.size'     => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'province.required'     => 'انتخاب استان محل خدمت الزامی است.',
            'city.required'         => 'انتخاب شهر محل خدمت الزامی است.',
        ]);

        $this->step = 4;
    }

    public function goToStep3()
    {
        $this->clearMessages();
        $this->step = 3;
    }

    // =========================================================================
    // STEP 4: SUBMIT REGISTRATION (HARMONIZED WITH /register & Technician Fields)
    // =========================================================================

    public function submitRegistration()
    {
        $this->clearMessages();

        if (!$this->isPhoneVerified) {
            $this->errorMessage = 'ابتدا شماره همراه خود را با کد تایید احراز فرمایید.';
            $this->step = 2;
            return;
        }

        $this->validate([
            'firstName'          => 'required|string|max:100',
            'lastName'           => 'required|string|max:100',
            'nationalCode'       => 'required|string|size:10',
            'province'           => 'required|string',
            'city'               => 'required|string',
            'address'            => 'nullable|string|max:500',
            'shabaNumber'        => 'nullable|string|max:34',
            'nationalCardImage'  => 'nullable|image|max:5120',
            'selectedCategories' => 'required|array|min:1',
        ], [
            'firstName.required'          => 'وارد کردن نام الزامی است.',
            'lastName.required'           => 'وارد کردن نام خانوادگی الزامی است.',
            'nationalCode.required'       => 'وارد کردن کد ملی ۱۰ رقمی الزامی است.',
            'nationalCode.size'           => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'province.required'           => 'انتخاب استان محل خدمت الزامی است.',
            'city.required'               => 'انتخاب شهر محل خدمت الزامی است.',
            'selectedCategories.required' => 'حداقل یک تخصص یا حوزه فنی را انتخاب نمایید.',
            'selectedCategories.min'      => 'حداقل یک تخصص یا حوزه فنی را انتخاب نمایید.',
            'nationalCardImage.image'     => 'فایل انتخابی برای کارت ملی باید تصویر معتبر (JPG, PNG) باشد.',
            'nationalCardImage.max'       => 'حجم تصویر کارت ملی حداکثر می‌تواند ۵ مگابایت باشد.',
        ]);

        // Upload national card image if provided (matching /register destination)
        $cardImagePath = null;
        if ($this->nationalCardImage) {
            $cardImagePath = $this->nationalCardImage->store('uploads/custom_fields', 'public');
        }

        // Resolve city coordinates
        $coveredCities = FieldServiceSetting::getCoveredCitiesWithCoords();
        $cityData = $coveredCities[$this->city] ?? ['lat' => 36.5444, 'lng' => 52.6780];

        // Format SHABA
        $formattedShaba = null;
        if (!empty(trim($this->shabaNumber))) {
            $cleanShaba = preg_replace('/[^0-9]/', '', $this->shabaNumber);
            $formattedShaba = 'IR' . $cleanShaba;
        }

        // Read registration approval setting for technician role
        $techRole = Role::where('name', 'technician')->first();
        $regSetting = \Modules\Settings\Entities\Setting::where('key', 'registration')->first();
        $regConfig = $regSetting && $regSetting->value ? json_decode($regSetting->value, true) : [];
        $approvalType = ($techRole && isset($regConfig[$techRole->id]['approval']))
            ? $regConfig[$techRole->id]['approval']
            : 'manual';

        $isManualApproval = ($approvalType === 'manual');
        $initialStatus = $isManualApproval
            ? \Modules\FieldService\App\Enums\TechnicianStatus::PENDING_APPROVAL
            : \Modules\FieldService\App\Enums\TechnicianStatus::ACTIVE;

        // Create direct technician with User account & role
        $tech = TechnicianLifecycleService::createDirectTechnician([
            'first_name'            => $this->firstName,
            'last_name'             => $this->lastName,
            'mobile'                => $this->phone,
            'national_code'         => $this->nationalCode,
            'shaba_number'          => $formattedShaba,
            'province'              => $this->province,
            'city'                  => $this->city,
            'address'               => $this->address,
            'lat'                   => $cityData['lat'] ?? 36.5444,
            'lng'                   => $cityData['lng'] ?? 52.6780,
            'operational_radius_km' => 25,
            'tier'                  => 'silver',
            'status'                => $initialStatus,
            'is_online'             => false,
            'categories'            => $this->selectedCategories,
            'national_card_image'   => $cardImagePath,
        ]);

        $user = $tech->user;

        // If manual approval, ensure record in registration_requests for admin review pipeline
        if ($isManualApproval) {
            \App\Models\RegistrationRequest::updateOrCreate(
                ['mobile' => $this->phone],
                [
                    'role_id'       => $techRole?->id,
                    'name'          => trim("{$this->firstName} {$this->lastName}"),
                    'email'         => $user->email,
                    'mobile'        => $this->phone,
                    'password'      => $user->password,
                    'status'        => 'pending',
                    'custom_fields' => [
                        'national_code'       => $this->nationalCode,
                        'province'            => $this->province,
                        'city'                => $this->city,
                        'address'             => $this->address,
                        'shaba_number'        => $formattedShaba,
                        'national_card_image' => $cardImagePath,
                        'categories'          => $this->selectedCategories,
                    ],
                ]
            );
        }

        // Save custom user field values if User has customValues relation
        if ($user && method_exists($user, 'customValues')) {
            $customItems = [
                'national_code'       => $this->nationalCode,
                'province'            => $this->province,
                'city'                => $this->city,
                'address'             => $this->address,
                'shaba_number'        => $formattedShaba,
                'national_card_image' => $cardImagePath,
            ];

            foreach ($customItems as $fieldName => $val) {
                if ($val !== null && $val !== '') {
                    $user->customValues()->updateOrCreate(
                        ['field_name' => $fieldName],
                        ['field_value' => (string) $val]
                    );
                }
            }
        }

        Auth::login($user, true);
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->isOpen = false;
        return $this->redirect(route('fieldservice.technician'), navigate: false);
    }

    public function logoutTechnician()
    {
        $user = Auth::user();
        if ($user) {
            $tech = Technician::where('user_id', $user->id)->first();
            if ($tech) {
                $tech->is_online = false;
                $tech->save();
            }
            Auth::logout();
            if (request()->hasSession()) {
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            }
        }

        return $this->redirect(route('fieldservice.technician'), navigate: false);
    }

    public function render()
    {
        $allProvinces = ProvinceCity::getProvinces();
        $allData = ProvinceCity::getAll(); // [ 'مازندران' => ['ساری', 'بابل', ...], ... ]
        $currentCities = ProvinceCity::getCities($this->province);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('fieldservice::livewire.technician.technician-auth-modal', [
            'allProvinces'   => $allProvinces,
            'allData'        => $allData,
            'currentCities'  => $currentCities,
            'categories'     => $categories,
        ]);
    }
}
