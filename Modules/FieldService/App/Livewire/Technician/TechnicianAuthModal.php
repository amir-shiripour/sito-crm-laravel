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
use Modules\FieldService\App\Support\TechnicianCustomFieldsHelper;
use Modules\Sms\Entities\SmsOtp;
use Spatie\Permission\Models\Role;

class TechnicianAuthModal extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;
    public bool $allowClose = true;

    // Step state: 1 = Phone Input & Detection, 2 = OTP Verification, 3 = Registration Fields (show_in_register)
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

    // Step 3: Registration details (harmonized with /admin/custom-fields show_in_register)
    public string $firstName = '';
    public string $lastName = '';
    public string $province = 'مازندران';
    public string $city = 'بابل';
    public array $customFields = [];
    public array $customFiles = [];

    // Alerts
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    protected $listeners = [
        'open-technician-auth'  => 'openModal',
        'close-technician-auth' => 'closeModal',
        'logout-technician'     => 'logoutTechnician',
    ];

    public function mount(bool $autoOpen = true, bool $allowClose = true)
    {
        $this->allowClose = $allowClose;
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
        $this->isOpen = $autoOpen;
        $this->initDefaultLocation();

        $phoneParam = request()->query('phone');
        if (!empty($phoneParam)) {
            $this->phone = self::normalizePhoneNumber($phoneParam);
        }
    }

    public function initDefaultLocation()
    {
        $this->province = 'مازندران';
        $cities = ProvinceCity::getCities('مازندران');
        $this->city = in_array('بابل', $cities, true) ? 'بابل' : ($cities[0] ?? 'بابل');

        $registerFieldNames = TechnicianCustomFieldsHelper::getFields('register')->pluck('field_name')->toArray();
        if (in_array('province', $registerFieldNames, true)) {
            $this->customFields['province'] = $this->province;
        }
        if (in_array('city', $registerFieldNames, true)) {
            $this->customFields['city'] = $this->city;
        }
    }

    public function updatedProvince($value)
    {
        $cities = ProvinceCity::getCities($value);
        if ($value === 'مازندران' && in_array('بابل', $cities, true)) {
            $this->city = 'بابل';
        } else {
            $this->city = $cities[0] ?? '';
        }
        $this->customFields['province'] = $value;
        $this->customFields['city'] = $this->city;
    }

    public function updatedCustomFields($value, $key)
    {
        if ($key === 'province') {
            $this->province = (string) $value;
            $this->updatedProvince($this->province);
        } elseif ($key === 'city') {
            $this->city = (string) $value;
        }
    }

    public function openModal(?string $phone = null)
    {
        $this->isOpen = true;
        $this->clearMessages();
        if (!empty($phone)) {
            $this->phone = self::normalizePhoneNumber($phone);
        }
    }

    public function closeModal()
    {
        if ($this->allowClose) {
            $this->isOpen = false;
            $this->clearMessages();
            return;
        }

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
                $this->successMessage = 'شماره همراه شما با موفقیت تایید شد. لطفاً اطلاعات ثبت‌نام را وارد فرمایید.';
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
        $this->successMessage = 'شماره همراه شما با موفقیت تایید شد. لطفاً اطلاعات ثبت‌نام را وارد فرمایید.';
    }

    // =========================================================================
    // STEP 3: SUBMIT REGISTRATION (ONLY show_in_register FIELDS)
    // =========================================================================

    public function submitRegistration()
    {
        $this->clearMessages();

        if (!$this->isPhoneVerified) {
            $this->errorMessage = 'ابتدا شماره همراه خود را با کد تایید احراز فرمایید.';
            $this->step = 2;
            return;
        }

        $registerFields = TechnicianCustomFieldsHelper::getFields('register');
        $dynamic = TechnicianCustomFieldsHelper::buildValidation($registerFields, [], false);

        $rules = array_merge([
            'firstName' => 'required|string|max:100',
            'lastName'  => 'required|string|max:100',
        ], $dynamic['rules']);

        $messages = array_merge([
            'firstName.required' => 'وارد کردن نام الزامی است.',
            'lastName.required'  => 'وارد کردن نام خانوادگی الزامی است.',
        ], $dynamic['messages']);

        $this->validate($rules, $messages);

        $provinceVal = !empty($this->customFields['province']) ? (string) $this->customFields['province'] : $this->province;
        $cityVal     = !empty($this->customFields['city']) ? (string) $this->customFields['city'] : $this->city;
        $nationalCodeVal = $this->customFields['national_code'] ?? null;
        $addressVal      = $this->customFields['address'] ?? null;

        // Format SHABA
        $formattedShaba = null;
        if (!empty(trim((string) ($this->customFields['shaba_number'] ?? '')))) {
            $cleanShaba = preg_replace('/[^0-9]/', '', (string) $this->customFields['shaba_number']);
            if ($cleanShaba !== '') {
                $formattedShaba = 'IR' . $cleanShaba;
            }
        }

        // Resolve city coordinates
        $coveredCities = FieldServiceSetting::getCoveredCitiesWithCoords();
        $cityData = $coveredCities[$cityVal] ?? ['lat' => 36.5444, 'lng' => 52.6780];

        // Create technician with INACTIVE status initially until verification in /user/profile is submitted
        $tech = TechnicianLifecycleService::createDirectTechnician([
            'first_name'            => $this->firstName,
            'last_name'             => $this->lastName,
            'mobile'                => $this->phone,
            'national_code'         => $nationalCodeVal,
            'shaba_number'          => $formattedShaba,
            'province'              => $provinceVal,
            'city'                  => $cityVal,
            'address'               => $addressVal,
            'lat'                   => $cityData['lat'] ?? 36.5444,
            'lng'                   => $cityData['lng'] ?? 52.6780,
            'operational_radius_km' => (int) \Modules\FieldService\App\Models\FieldServiceSetting::get('default_technician_radius_km', 25),
            'tier'                  => 'silver',
            'status'                => \Modules\FieldService\App\Enums\TechnicianStatus::INACTIVE,
            'is_online'             => false,
            'meta'                  => ['verification_submitted' => false],
        ]);

        $user = $tech->user;

        // Persist all dynamic registration custom fields to UserCustomValue & Technician
        $savedCustomValues = TechnicianCustomFieldsHelper::syncValues(
            $user,
            $tech,
            $registerFields,
            $this->customFields,
            $this->customFiles
        );

        Auth::login($user, true);
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->isOpen = false;

        // If verification/profile is still needed, restrict & redirect to /user/profile
        if (TechnicianCustomFieldsHelper::needsVerification($user, $tech)) {
            session()->flash('warning', 'ثبت‌نام شما انجام شد. لطفاً اطلاعات احراز هویت و تخصص‌های خود را در این صفحه تکمیل فرمایید.');
            return $this->redirect(url('/user/profile'), navigate: false);
        }

        // Otherwise finalize approval status immediately
        $techRole = Role::where('name', 'technician')->first();
        $regSetting = \Modules\Settings\Entities\Setting::where('key', 'registration')->first();
        $regConfig = $regSetting && $regSetting->value ? json_decode($regSetting->value, true) : [];
        $approvalType = ($techRole && isset($regConfig[$techRole->id]['approval']))
            ? $regConfig[$techRole->id]['approval']
            : 'manual';

        $isManualApproval = ($approvalType === 'manual');
        $tech->status = $isManualApproval
            ? \Modules\FieldService\App\Enums\TechnicianStatus::PENDING_APPROVAL
            : \Modules\FieldService\App\Enums\TechnicianStatus::ACTIVE;
        if (!$isManualApproval) {
            $tech->verified_at = now();
        }
        $meta = is_array($tech->meta) ? $tech->meta : [];
        $meta['verification_submitted'] = true;
        $tech->meta = $meta;
        $tech->save();

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
                    'custom_fields' => $savedCustomValues,
                ]
            );
        }

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
        $allData = ProvinceCity::getAll();
        $registerFields = TechnicianCustomFieldsHelper::getFields('register');

        return view('fieldservice::livewire.technician.technician-auth-modal', [
            'allData'        => $allData,
            'registerFields' => $registerFields,
        ]);
    }
}
