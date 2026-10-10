<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertyHost;
use Modules\Properties\Entities\PropertyOwner;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertySetting;
use App\Models\User;
use App\Models\CustomUserField;
use App\Models\UserCustomValue;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Str;

class HostController extends Controller
{
    use FileUploadTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * صفحه ثبت‌نام و درخواست میزبانی
     */
    public function becomeHost()
    {
        $user = auth()->user();
        $multiHostEnabled = (bool) PropertySetting::get('rental_multi_host_enabled', 0);

        if (!$multiHostEnabled) {
            return redirect()->route('user.properties.index')->with('error', 'سیستم میزبانی در حال حاضر غیرفعال است.');
        }

        $host = PropertyHost::where('user_id', $user->id)->first();
        if ($host) {
            return redirect()->route('user.properties.hosts.dashboard');
        }

        // یافتن مالک متناظر در صورت وجود
        $existingOwner = PropertyOwner::where('phone', $user->mobile ?? $user->phone)->orWhere('user_id', $user->id)->first();

        return view('properties::user.hosts.register', compact('user', 'existingOwner'));
    }

    /**
     * ثبت درخواست میزبانی
     */
    public function storeHostRequest(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'display_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'about' => 'nullable|string|max:1000',
            'shaba_number' => 'nullable|string|max:35',
            'bank_name' => 'nullable|string|max:100',
            'account_owner_name' => 'nullable|string|max:255',
            'national_code' => 'nullable|string|max:15',
            'national_card_image' => 'nullable|image|max:5120',
            'avatar' => 'nullable|image|max:3072',
        ]);

        $autoApprove = (bool) PropertySetting::get('rental_host_auto_approve', 0);

        // هماهنگی با مدیریت مالکان (PropertyOwner)
        $owner = PropertyOwner::where('user_id', $user->id)
            ->orWhere('phone', $request->phone)
            ->first();

        if (!$owner) {
            $nameParts = explode(' ', $user->name ?? $request->display_name, 2);
            $firstName = $nameParts[0] ?? $request->display_name;
            $lastName = $nameParts[1] ?? ' ';

            $owner = PropertyOwner::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $request->phone,
                'created_by' => $user->id,
            ]);
        } else {
            if (!$owner->user_id) {
                $owner->update(['user_id' => $user->id]);
            }
        }

        $data = [
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'display_name' => $request->display_name,
            'slug' => Str::slug($request->display_name) . '-' . $user->id,
            'phone' => $request->phone,
            'about' => $request->about,
            'shaba_number' => $request->shaba_number,
            'bank_name' => $request->bank_name,
            'account_owner_name' => $request->account_owner_name,
            'national_code' => $request->national_code,
            'status' => $autoApprove ? 'active' : 'pending',
            'kyc_status' => $request->filled('national_code') ? 'pending' : 'not_submitted',
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->uploadFile($request->file('avatar'), 'properties/hosts/avatars');
        }

        if ($request->hasFile('national_card_image')) {
            $data['national_card_image'] = $this->uploadFile($request->file('national_card_image'), 'properties/hosts/kyc');
            $data['kyc_status'] = 'pending';
        }

        $host = PropertyHost::create($data);

        // در صورت تایید خودکار، نقش داده شود
        if ($autoApprove) {
            $user->assignRole('property_host');
            $msg = 'حساب میزبانی شما با موفقیت فعال شد. اکنون می‌توانید اقامتگاه‌های خود را ثبت کنید.';
        } else {
            $msg = 'درخواست میزبانی شما ثبت شد و پس از بررسی توسط مدیریت پلتفرم فعال خواهد شد.';
        }

        return redirect()->route('user.properties.hosts.dashboard')->with('success', $msg);
    }

    /**
     * داشبورد اختصاصی میزبان
     */
    public function dashboard()
    {
        $user = auth()->user();
        $host = PropertyHost::where('user_id', $user->id)->first();

        if (!$host) {
            return redirect()->route('user.properties.hosts.register');
        }

        $properties = Property::where('host_id', $host->id)->with('rentalConfig', 'status')->latest()->get();

        $stats = [
            'total' => $properties->count(),
            'approved' => $properties->where('approval_status', 'approved')->where('publication_status', 'published')->count(),
            'pending' => $properties->where('approval_status', 'pending_review')->count(),
            'rejected' => $properties->where('approval_status', 'rejected')->count(),
        ];

        // تعیین دسترسی میزبان به ثبت اقامتگاه جدید بر اساس وضعیت و تنظیمات سیستم
        $pendingCanCreate = (bool) PropertySetting::get('rental_pending_host_can_create', 0);
        $canCreateProperty = ($host->status === 'active') || ($host->status === 'pending' && $pendingCanCreate);

        return view('properties::user.hosts.dashboard', compact('host', 'properties', 'stats', 'canCreateProperty', 'pendingCanCreate'));
    }

    /**
     * پروفایل و اطلاعات مالی میزبان
     */
    public function profile()
    {
        $user = auth()->user();
        $host = PropertyHost::where('user_id', $user->id)->firstOrFail();

        $roleNames = $user->roles->pluck('name')->toArray();
        if (!in_array('property_host', $roleNames)) {
            $roleNames[] = 'property_host';
        }

        $customFields = CustomUserField::whereIn('role_name', $roleNames)
            ->where('show_in_profile', true)
            ->orderBy('id')
            ->get()
            ->unique('field_name');

        $customValues = UserCustomValue::where('user_id', $user->id)
            ->get()
            ->keyBy('field_name');

        $settlementRulesTitle = PropertySetting::get('rental_settlement_rules_title', 'قوانین و رویه تسویه حساب درآمد:');
        $rawRules = PropertySetting::get('rental_settlement_rules');
        $settlementRules = $rawRules ? json_decode($rawRules, true) : null;
        if (empty($settlementRules) || !is_array($settlementRules)) {
            $settlementRules = [
                [
                    'title' => 'زمان تسویه',
                    'text' => 'مبالغ رزروها پس از تحویل اقامتگاه به مسافر و ورود بدون مغایرت در اولین سیکل پایا واریز می‌گردد.',
                ],
                [
                    'title' => 'کارمزد پلتفرم',
                    'text' => 'سهم پلتفرم از هر رزرو {commission}٪ بوده و مابقی مستقیماً به شبا واریز می‌شود.',
                ],
                [
                    'title' => 'تطابق حساب',
                    'text' => 'نام صاحب حساب باید با اطلاعات هویتی و کد ملی همخوانی کامل داشته باشد.',
                ],
            ];
        }

        return view('properties::user.hosts.profile', compact(
            'host',
            'customFields',
            'customValues',
            'settlementRulesTitle',
            'settlementRules'
        ));
    }

    /**
     * ویرایش اطلاعات میزبان
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $host = PropertyHost::where('user_id', $user->id)->firstOrFail();

        // اعتبارسنجی اطلاعات پایه میزبان (بدون هاردکد کردن مدارک)
        $request->validate([
            'display_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'about' => 'nullable|string|max:1000',
            'shaba_number' => 'nullable|string|max:35',
            'bank_name' => 'nullable|string|max:100',
            'account_owner_name' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:3072',
        ]);

        $roleNames = $user->roles->pluck('name')->toArray();
        if (!in_array('property_host', $roleNames)) {
            $roleNames[] = 'property_host';
        }

        // استخراج و اعتبارسنجی پویا بر اساس فیلدهای سفارشی احراز هویت
        $customFields = CustomUserField::whereIn('role_name', $roleNames)
            ->where('show_in_profile', true)
            ->orderBy('id')
            ->get()
            ->unique('field_name');

        $customRules = [];
        $customMessages = [];
        $customAttributes = [];

        foreach ($customFields as $f) {
            $key = 'custom.' . $f->field_name;
            $type = strtolower($f->field_type ?? 'text');
            $meta = is_array($f->meta ?? null) ? $f->meta : (is_string($f->meta ?? null) ? json_decode($f->meta, true) : []);
            $meta = $meta ?: [];

            $existingVal = UserCustomValue::where('user_id', $user->id)
                ->where('field_name', $f->field_name)
                ->value('value');

            if ($f->is_required) {
                if ($type === 'file') {
                    $base = !empty($existingVal) ? ['nullable'] : ['required'];
                } else {
                    $base = ['required'];
                }
            } else {
                $base = ['nullable'];
            }

            switch ($type) {
                case 'number': $base[] = 'numeric'; break;
                case 'date': $base[] = 'date'; break;
                case 'email': $base[] = 'email'; break;
                case 'file':
                    $base[] = 'file';
                    $base[] = 'max:10240';
                    break;
                case 'checkbox':
                    $base[] = 'array';
                    break;
                case 'select':
                case 'radio':
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $base[] = 'in:' . implode(',', array_values($meta['options']));
                    } else {
                        $base[] = 'string';
                    }
                    break;
                default:
                    $base[] = 'string';
            }

            if (!empty($f->rules) && is_array($f->rules)) {
                $base = array_merge($base, $f->rules);
            }

            $customRules[$key] = $base;
            $label = $f->label ?: $f->field_name;
            $customAttributes[$key] = $label;
            $customMessages[$key . '.required'] = "تکمیل فیلد «{$label}» الزامی است.";
        }

        if (!empty($customRules)) {
            $request->validate($customRules, $customMessages, $customAttributes);
        }

        $data = [
            'display_name' => $request->display_name,
            'phone' => $request->phone,
            'about' => $request->about,
            'shaba_number' => $request->shaba_number,
            'bank_name' => $request->bank_name,
            'account_owner_name' => $request->account_owner_name,
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->uploadFile($request->file('avatar'), 'properties/hosts/avatars');
        }

        $hasVerificationChanges = false;
        foreach ($customFields as $f) {
            $fieldName = $f->field_name;
            $type = strtolower($f->field_type ?? 'text');

            if ($type === 'file') {
                if ($request->hasFile("custom.{$fieldName}")) {
                    $file = $request->file("custom.{$fieldName}");
                    $path = $this->uploadFile($file, 'properties/hosts/kyc');
                    UserCustomValue::updateOrCreate(
                        ['user_id' => $user->id, 'field_name' => $fieldName],
                        ['value' => $path]
                    );
                    if ($fieldName === 'national_card_image') {
                        $data['national_card_image'] = $path;
                    }
                    $hasVerificationChanges = true;
                }
            } else {
                if ($request->has("custom.{$fieldName}")) {
                    $val = $request->input("custom.{$fieldName}");
                    if (is_array($val)) {
                        $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                    }
                    UserCustomValue::updateOrCreate(
                        ['user_id' => $user->id, 'field_name' => $fieldName],
                        ['value' => $val]
                    );
                    if ($fieldName === 'national_code') {
                        $data['national_code'] = $val;
                    }
                    $hasVerificationChanges = true;
                }
            }
        }

        // احراز هویت مجدد: در صورت هرگونه تغییر در اطلاعات یا مدارک احراز هویت، مدارک مجدداً جهت بررسی به صف انتظار می‌روند
        if ($hasVerificationChanges) {
            $data['kyc_status'] = 'pending';
            $data['kyc_rejection_reason'] = null;
        }

        $host->update($data);

        return back()->with('success', 'اطلاعات با موفقیت ذخیره شد.');
    }

    /**
     * لیست میزبان‌ها برای مدیریت پلتفرم (Admin)
     */
    public function adminIndex(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole(['super-admin', 'admin']) && !$user->can('properties.hosts.view') && !$user->can('properties.manage')) {
            abort(403);
        }

        $query = PropertyHost::with(['user.customValues', 'owner', 'properties' => function($q) {
            $q->select('id', 'host_id', 'title', 'code', 'listing_type', 'publication_status', 'approval_status', 'price', 'created_at')
              ->latest()
              ->limit(10);
        }])->withCount('properties');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('display_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('national_code', 'like', "%{$s}%")
                  ->orWhereHas('user', function($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  })
                  ->orWhereHas('properties', function($pq) use ($s) {
                      $pq->where('title', 'like', "%{$s}%")
                         ->orWhere('code', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        $hosts = $query->latest()->paginate(15)->withQueryString();

        $verificationFieldDefs = CustomUserField::whereIn('role_name', ['property_host', 'host'])
            ->where('show_in_profile', true)
            ->orderBy('id')
            ->get();

        return view('properties::user.hosts.index', compact('hosts', 'verificationFieldDefs'));
    }

    /**
     * ویرایش کامل اطلاعات میزبان توسط ادمین
     */
    public function adminUpdate(Request $request, PropertyHost $host)
    {
        $user = auth()->user();
        if (!$user->hasRole(['super-admin', 'admin']) && !$user->can('properties.hosts.manage') && !$user->can('properties.manage')) {
            abort(403);
        }

        $request->validate([
            'display_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'about' => 'nullable|string|max:1000',
            'shaba_number' => 'nullable|string|max:35',
            'bank_name' => 'nullable|string|max:100',
            'account_owner_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,pending,suspended',
            'kyc_status' => 'required|in:approved,pending,rejected,not_submitted',
            'kyc_rejection_reason' => 'nullable|string|max:500',
        ], [
            'display_name.required' => 'نام نمایشی میزبان الزامی است.',
            'phone.required' => 'شماره تماس میزبان الزامی است.',
            'status.required' => 'وضعیت حساب میزبان الزامی است.',
            'kyc_status.required' => 'وضعیت احراز هویت الزامی است.',
        ]);

        $data = [
            'display_name' => $request->display_name,
            'phone' => $request->phone,
            'commission_rate' => $request->filled('commission_rate') ? $request->commission_rate : null,
            'about' => $request->about,
            'shaba_number' => $request->shaba_number,
            'bank_name' => $request->bank_name,
            'account_owner_name' => $request->account_owner_name,
            'status' => $request->status,
            'kyc_status' => $request->kyc_status,
            'kyc_rejection_reason' => ($request->kyc_status === 'rejected' || $request->status === 'suspended')
                ? $request->kyc_rejection_reason
                : null,
        ];

        // در صورت فعال‌سازی حساب، نقش property_host به کاربر اختصاص داده شود
        if ($data['status'] === 'active' && $host->user && !$host->user->hasRole('property_host')) {
            $host->user->assignRole('property_host');
        }

        // پردازش فیلدهای سفارشی احراز هویت در صورت ارسال توسط ادمین
        if ($host->user_id && $request->has('custom') && is_array($request->custom)) {
            foreach ($request->custom as $fieldName => $val) {
                if ($request->hasFile("custom.{$fieldName}")) {
                    $file = $request->file("custom.{$fieldName}");
                    $path = $this->uploadFile($file, 'properties/hosts/kyc');
                    UserCustomValue::updateOrCreate(
                        ['user_id' => $host->user_id, 'field_name' => $fieldName],
                        ['value' => $path]
                    );
                    if ($fieldName === 'national_card_image') {
                        $data['national_card_image'] = $path;
                    }
                } elseif (!is_null($val)) {
                    if (is_array($val)) {
                        $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                    }
                    UserCustomValue::updateOrCreate(
                        ['user_id' => $host->user_id, 'field_name' => $fieldName],
                        ['value' => $val]
                    );
                    if ($fieldName === 'national_code') {
                        $data['national_code'] = $val;
                    }
                }
            }
        }

        $host->update($data);

        return back()->with('success', "اطلاعات میزبان «{$host->display_name}» با موفقیت ویرایش و ذخیره شد.");
    }

    /**
     * تایید میزبان توسط ادمین
     */
    public function approveHost(PropertyHost $host)
    {
        $user = auth()->user();
        if (!$user->hasRole(['super-admin', 'admin']) && !$user->can('properties.hosts.manage') && !$user->can('properties.manage')) {
            abort(403);
        }

        $host->update([
            'status' => 'active',
            'kyc_status' => 'approved',
            'kyc_rejection_reason' => null,
        ]);

        if ($host->user && !$host->user->hasRole('property_host')) {
            $host->user->assignRole('property_host');
        }

        return back()->with('success', "میزبان «{$host->display_name}» با موفقیت تأیید و فعال شد.");
    }

    /**
     * تایید اختصاصی مدارک هویتی میزبان (KYC)
     */
    public function approveKyc(PropertyHost $host)
    {
        $user = auth()->user();
        if (!$user->hasRole(['super-admin', 'admin']) && !$user->can('properties.hosts.manage') && !$user->can('properties.manage')) {
            abort(403);
        }

        $host->update([
            'kyc_status' => 'approved',
            'kyc_rejection_reason' => null,
        ]);

        return back()->with('success', "مدارک احراز هویت میزبان «{$host->display_name}» با موفقیت تأیید شد.");
    }

    /**
     * رد یا تعلیق میزبان توسط ادمین
     */
    public function rejectHost(Request $request, PropertyHost $host)
    {
        $user = auth()->user();
        if (!$user->hasRole(['super-admin', 'admin']) && !$user->can('properties.hosts.manage') && !$user->can('properties.manage')) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $host->update([
            'status' => 'suspended',
            'kyc_status' => 'rejected',
            'kyc_rejection_reason' => $request->reason,
        ]);

        return back()->with('success', "میزبان «{$host->display_name}» رد/تعلیق شد.");
    }

    /**
     * جستجوی سریع میزبان‌ها (Ajax Search)
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            $hosts = PropertyHost::where('status', 'active')
                ->latest()
                ->limit(10)
                ->get(['id', 'display_name', 'phone', 'avatar', 'status', 'kyc_status']);
            return response()->json($hosts);
        }

        $hosts = PropertyHost::where(function ($q) use ($query) {
            $q->where('display_name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('national_code', 'like', "%{$query}%");
        })
        ->limit(15)
        ->get(['id', 'display_name', 'phone', 'avatar', 'status', 'kyc_status']);

        return response()->json($hosts);
    }
}

