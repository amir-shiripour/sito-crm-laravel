<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertyHost;
use Modules\Properties\Entities\PropertyOwner;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertySetting;
use App\Models\User;
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

        return view('properties::user.hosts.dashboard', compact('host', 'properties', 'stats'));
    }

    /**
     * پروفایل و اطلاعات مالی میزبان
     */
    public function profile()
    {
        $user = auth()->user();
        $host = PropertyHost::where('user_id', $user->id)->firstOrFail();

        return view('properties::user.hosts.profile', compact('host'));
    }

    /**
     * ویرایش اطلاعات میزبان
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $host = PropertyHost::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'display_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'about' => 'nullable|string|max:1000',
            'shaba_number' => 'nullable|string|max:35',
            'bank_name' => 'nullable|string|max:100',
            'account_owner_name' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:3072',
            'national_code' => 'nullable|string|max:15',
            'national_card_image' => 'nullable|image|max:5120',
        ]);

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

        // احراز هویت مجدد فقط در صورت نیاز
        if ($request->filled('national_code') && $host->kyc_status !== 'approved') {
            $data['national_code'] = $request->national_code;
        }

        if ($request->hasFile('national_card_image') && $host->kyc_status !== 'approved') {
            $data['national_card_image'] = $this->uploadFile($request->file('national_card_image'), 'properties/hosts/kyc');
            $data['kyc_status'] = 'pending';
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

        $query = PropertyHost::with('user', 'owner')->withCount('properties');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('display_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('national_code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        $hosts = $query->latest()->paginate(15)->withQueryString();

        return view('properties::user.hosts.index', compact('hosts'));
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

