<?php

namespace Modules\FieldService\App\Livewire\Technician;

use App\Models\User;
use App\Services\RegistrationOtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\FieldServiceSetting;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\TechnicianLifecycleService;
use Modules\Sms\Entities\SmsOtp;
use Spatie\Permission\Models\Role;

class TechnicianAuthModal extends Component
{
    // Mode & Tab
    public string $tab = 'otp';   // 'otp' | 'password'
    public bool $isOpen = false;

    // Password Login State
    public string $username = '';
    public string $password = '';
    public bool $remember = true;
    public int $pwdStep = 1; // 1: username, 2: password

    // OTP Login State
    public string $otpUsername = '';
    public string $otpCode = '';
    public bool $otpSent = false;
    public int $otpResendIn = 60;
    public ?string $devOtpCode = null;

    // Registration State (when technician not found)
    public bool $isPhoneVerified = false;
    public bool $isRegister = false;
    public string $registerFirstName = '';
    public string $registerLastName = '';
    public string $registerNationalCode = '';
    public string $registerCity = 'بابل';
    public array $registerCategories = [];

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

        // If user is already authenticated with technician role and record
        if ($user && $user->hasRole('technician')) {
            $tech = Technician::where('user_id', $user->id)->first();
            if ($tech) {
                $this->isOpen = false;
                return;
            }
        }

        // If not authenticated or not a valid technician, open modal
        $this->isOpen = true;
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

    public function switchTab(string $newTab)
    {
        $this->tab = $newTab;
        $this->clearMessages();
        $this->isRegister = false;
    }

    public static function normalizePhoneNumber(?string $number): string
    {
        return RegistrationOtpService::normalizePhoneNumber($number);
    }

    public function sendOtp()
    {
        $this->clearMessages();
        $phone = self::normalizePhoneNumber($this->otpUsername);

        if (empty($phone) || strlen($phone) !== 11 || !str_starts_with($phone, '09')) {
            $this->errorMessage = 'لطفاً یک شماره همراه معتبر ۱۱ رقمی (مثال: 09112223344) وارد کنید.';
            return;
        }

        $this->otpUsername = $phone;
        $res = RegistrationOtpService::sendOtp($phone);

        if (!$res['success']) {
            $this->errorMessage = $res['message'] ?? 'خطا در ارسال کد تایید.';
            if (!empty($res['resend_in'])) {
                $this->otpResendIn = (int) $res['resend_in'];
            }
            return;
        }

        $this->otpSent = true;
        $this->otpResendIn = (int) ($res['resend_in'] ?? 60);

        // Fetch latest OTP code from database for instant dev convenience if local/debug
        if (config('app.debug') || app()->environment('local', 'testing')) {
            $latestOtp = SmsOtp::where('phone', $phone)->where('context', RegistrationOtpService::CONTEXT)->latest()->first();
            if ($latestOtp) {
                $this->devOtpCode = $latestOtp->code;
                $this->successMessage = "کد ورود به شماره {$phone} ارسال شد. (کد تستی: {$latestOtp->code})";
                return;
            }
        }

        $this->successMessage = "کد تایید ورود به شماره {$phone} پیامک شد.";
    }

    public function resetOtp()
    {
        $this->otpSent = false;
        $this->otpCode = '';
        $this->devOtpCode = null;
        $this->isPhoneVerified = false;
        $this->isRegister = false;
        $this->clearMessages();
    }

    public function verifyOtp()
    {
        $this->clearMessages();
        $phone = self::normalizePhoneNumber($this->otpUsername);

        if (!$this->isPhoneVerified) {
            $code = trim(self::normalizePhoneNumber($this->otpCode));

            if (empty($phone) || empty($code)) {
                $this->errorMessage = 'لطفاً کد تایید پیامکی را به طور کامل وارد کنید.';
                return;
            }

            $verify = RegistrationOtpService::verifyOtp($phone, $code);
            if (!$verify['success']) {
                $this->errorMessage = $verify['message'] ?? 'کد تایید وارد شده نامعتبر یا منقضی است.';
                return;
            }

            $this->isPhoneVerified = true;
        }

        // Check if a Technician record or User exists for this phone
        $tech = Technician::where('mobile', $phone)->first();
        $user = User::where('mobile', $phone)->first();

        // If neither exists and registration form is not filled yet, switch to register mode
        if (!$tech && !$user && !$this->isRegister) {
            $this->isRegister = true;
            $this->successMessage = 'شماره همراه شما تایید شد. لطفاً مشخصات خود را جهت ثبت و ورود به ناوگان تکمیل نمایید.';
            return;
        }

        // If in registration mode, validate and create
        if ($this->isRegister) {
            $this->validate([
                'registerFirstName'    => 'required|string|max:60',
                'registerLastName'     => 'required|string|max:60',
                'registerNationalCode' => 'nullable|string|size:10',
                'registerCity'         => 'required|string',
                'registerCategories'   => 'required|array|min:1',
            ], [
                'registerFirstName.required'  => 'نام الزامی است.',
                'registerLastName.required'   => 'نام خانوادگی الزامی است.',
                'registerNationalCode.size'   => 'کد ملی باید ۱۰ رقم باشد.',
                'registerCity.required'       => 'انتخاب شهر محل خدمت الزامی است.',
                'registerCategories.required' => 'حداقل یک تخصص را انتخاب نمایید.',
                'registerCategories.min'      => 'حداقل یک تخصص را انتخاب نمایید.',
            ]);

            $coveredCities = FieldServiceSetting::getCoveredCitiesWithCoords();
            $cityData = $coveredCities[$this->registerCity] ?? ['lat' => 36.5444, 'lng' => 52.6780];

            $tech = TechnicianLifecycleService::createDirectTechnician([
                'first_name'            => $this->registerFirstName,
                'last_name'             => $this->registerLastName,
                'mobile'                => $phone,
                'national_code'         => $this->registerNationalCode,
                'province'              => 'مازندران',
                'city'                  => $this->registerCity,
                'lat'                   => $cityData['lat'] ?? 36.5444,
                'lng'                   => $cityData['lng'] ?? 52.6780,
                'operational_radius_km' => 25,
                'status'                => 'active',
                'tier'                  => 'silver',
                'categories'            => $this->registerCategories,
            ]);

            $user = $tech->user;
        } else {
            // Existing tech or existing user
            if (!$user) {
                $user = User::create([
                    'name'            => $tech ? $tech->full_name : 'تکنسین نصاب‌باشی',
                    'mobile'          => $phone,
                    'email'           => 'tech_' . $phone . '@crm.local',
                    'password'        => Hash::make(Str::random(24)),
                    'password_set_at' => now(),
                ]);
            }

            // Ensure Spatie role 'technician'
            $role = Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web'], [
                'display_name' => 'متخصص و تکنسین فنی (نصاب‌باشی)',
            ]);
            if (!$user->hasRole('technician')) {
                $user->assignRole($role);
            }

            // Sync with Technician model
            if ($tech) {
                if ($tech->user_id !== $user->id) {
                    $tech->user_id = $user->id;
                    $tech->save();
                }
            } else {
                $tech = TechnicianLifecycleService::createOrUpdateTechnicianForUser($user, [
                    'mobile' => $phone,
                ]);
            }
        }

        // Login user
        Auth::login($user, $this->remember);
        request()->session()->regenerate();

        $this->isOpen = false;
        return $this->redirect(route('fieldservice.technician'), navigate: false);
    }

    public function checkPasswordStep()
    {
        $this->clearMessages();
        $username = trim($this->username);

        if (empty($username)) {
            $this->errorMessage = 'لطفاً شماره همراه، ایمیل یا نام کاربری خود را وارد کنید.';
            return;
        }

        $phone = self::normalizePhoneNumber($username);

        $user = User::where(function ($q) use ($username, $phone) {
            $q->where('email', $username)
              ->orWhere('name', $username);
            if (!empty($phone)) {
                $q->orWhere('mobile', $phone);
            }
        })->first();

        if (!$user) {
            $this->errorMessage = 'حساب کاربری با این مشخصات یافت نشد. لطفاً از تب ورود با پیامک استفاده کنید.';
            return;
        }

        $this->pwdStep = 2;
    }

    public function loginWithPassword()
    {
        $this->clearMessages();
        $username = trim($this->username);
        $password = trim($this->password);

        if (empty($password)) {
            $this->errorMessage = 'لطفاً رمز عبور خود را وارد کنید.';
            return;
        }

        $phone = self::normalizePhoneNumber($username);

        $user = User::where(function ($q) use ($username, $phone) {
            $q->where('email', $username)
              ->orWhere('name', $username);
            if (!empty($phone)) {
                $q->orWhere('mobile', $phone);
            }
        })->first();

        if (!$user || !Hash::check($password, $user->password)) {
            $this->errorMessage = 'رمز عبور وارد شده نادرست است.';
            return;
        }

        // Ensure role and technician record
        $role = Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web'], [
            'display_name' => 'متخصص و تکنسین فنی (نصاب‌باشی)',
        ]);
        if (!$user->hasRole('technician')) {
            $user->assignRole($role);
        }

        $tech = Technician::where('user_id', $user->id)->first();
        if (!$tech && !empty($user->mobile)) {
            $tech = Technician::where('mobile', $user->mobile)->first();
            if ($tech) {
                $tech->user_id = $user->id;
                $tech->save();
            }
        }

        if (!$tech) {
            $tech = TechnicianLifecycleService::createOrUpdateTechnicianForUser($user, [
                'mobile' => $user->mobile,
            ]);
        }

        Auth::login($user, $this->remember);
        request()->session()->regenerate();

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
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return $this->redirect(route('fieldservice.technician'), navigate: false);
    }

    public function render()
    {
        $coveredCities = FieldServiceSetting::getCoveredCitiesWithCoords();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('fieldservice::livewire.technician.technician-auth-modal', [
            'coveredCities' => $coveredCities,
            'categories'    => $categories,
        ]);
    }
}
