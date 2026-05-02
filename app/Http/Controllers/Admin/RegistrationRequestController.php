<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use App\Models\User;
use App\Models\CustomUserField; // مدل اضافه شد
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationRequestController extends Controller
{
    public function index()
    {
        $requests = RegistrationRequest::with('role', 'reviewer')->latest()->paginate(10);

        // واکشی تمام فیلدهای سفارشی و کلیدگذاری آنها بر اساس field_name برای پیدا کردن لیبل فارسی
        $customFields = CustomUserField::all()->keyBy('field_name');

        return view('admin.registration_requests.index', compact('requests', 'customFields'));
    }

    public function approve(RegistrationRequest $registrationRequest)
    {
        $user = User::create([
            'name' => $registrationRequest->name,
            'email' => $registrationRequest->email,
            'mobile' => $registrationRequest->mobile,
            'password' => $registrationRequest->password,
        ]);

        $user->assignRole($registrationRequest->role);

        // ذخیره فیلدهای سفارشی
        if (!empty($registrationRequest->custom_fields)) {
            foreach ($registrationRequest->custom_fields as $fieldName => $value) {
                $user->customValues()->create([
                    'field_name' => $fieldName,
                    'field_value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }
        }

        $registrationRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.registration-requests.index')->with('success', 'درخواست ثبت‌نام با موفقیت تایید شد و کاربر ایجاد گردید.');
    }

    public function reject(Request $request, RegistrationRequest $registrationRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $registrationRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.registration-requests.index')->with('success', 'درخواست ثبت‌نام با موفقیت رد شد.');
    }
}
