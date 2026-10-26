<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use App\Models\User;
use App\Models\CustomUserField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RegistrationRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = RegistrationRequest::with('role', 'reviewer')->latest();

        // Status filter
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->status);
        }

        // Role filter
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Search filter (name, email, mobile)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('mobile', 'like', "%{$s}%");
            });
        }

        $requests = $query->paginate(12)->withQueryString();

        // Custom fields lookup
        $customFields = CustomUserField::all()->keyBy('field_name');

        // FieldService categories lookup
        $categoriesMap = [];
        if (class_exists(\Modules\FieldService\App\Models\Category::class)) {
            $categoriesMap = \Modules\FieldService\App\Models\Category::all()->keyBy('id');
        }

        // Status counter metrics
        $counts = [
            'total'    => RegistrationRequest::count(),
            'pending'  => RegistrationRequest::where('status', 'pending')->count(),
            'approved' => RegistrationRequest::where('status', 'approved')->count(),
            'rejected' => RegistrationRequest::where('status', 'rejected')->count(),
        ];

        $roles = Role::all();

        return view('admin.registration_requests.index', compact('requests', 'customFields', 'categoriesMap', 'counts', 'roles'));
    }

    public function approve(RegistrationRequest $registrationRequest)
    {
        $isHost = ($registrationRequest->role && $registrationRequest->role->name === 'property_host');

        // Check if user already exists (e.g. from technician web app registration)
        $user = null;
        if (!empty($registrationRequest->mobile)) {
            $user = User::where('mobile', $registrationRequest->mobile)->first();
        }
        if (!$user && !empty($registrationRequest->email)) {
            $user = User::where('email', $registrationRequest->email)->first();
        }

        if (!$user) {
            $user = User::create([
                'name'            => $registrationRequest->name,
                'email'           => $registrationRequest->email,
                'mobile'          => $registrationRequest->mobile,
                'password'        => $registrationRequest->password,
                'password_set_at' => $isHost ? null : now(),
            ]);
        } else {
            if (empty($user->name) || $user->name === 'کاربر') {
                $user->update(['name' => $registrationRequest->name]);
            }
        }

        if ($registrationRequest->role && !$user->hasRole($registrationRequest->role)) {
            $user->assignRole($registrationRequest->role);
        }

        // Save / update custom user values
        if (!empty($registrationRequest->custom_fields) && method_exists($user, 'customValues')) {
            foreach ($registrationRequest->custom_fields as $fieldName => $value) {
                if ($value !== null && $value !== '') {
                    $user->customValues()->updateOrCreate(
                        ['field_name' => $fieldName],
                        ['field_value' => is_array($value) ? json_encode($value) : (string) $value]
                    );
                }
            }
        }

        // Host lifecycle synchronization
        if ($registrationRequest->role && $registrationRequest->role->name === 'property_host') {
            if (class_exists(\Modules\Properties\Services\HostLifecycleService::class)) {
                \Modules\Properties\Services\HostLifecycleService::createOrUpdateHostForUser($user, [
                    'phone'        => $user->mobile,
                    'display_name' => $user->name,
                ]);
            }
        }

        // FieldService Technician lifecycle synchronization
        if ($registrationRequest->role && in_array($registrationRequest->role->name, ['technician', 'field_service_technician'], true)) {
            if (class_exists(\Modules\FieldService\App\Services\TechnicianLifecycleService::class)) {
                $customFields = $registrationRequest->custom_fields ?? [];
                \Modules\FieldService\App\Services\TechnicianLifecycleService::createOrUpdateTechnicianForUser($user, array_merge($customFields, [
                    'status' => \Modules\FieldService\App\Enums\TechnicianStatus::ACTIVE,
                ]));
            }
        }

        $registrationRequest->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', "درخواست عضویت «{$registrationRequest->name}» با موفقیت تایید شد و دسترسی‌های وی فعال گردید.");
    }

    public function reject(Request $request, RegistrationRequest $registrationRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $registrationRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by'      => Auth::id(),
            'reviewed_at'      => now(),
        ]);

        // If technician exists, suspend/deactivate
        if ($registrationRequest->role && in_array($registrationRequest->role->name, ['technician', 'field_service_technician'], true)) {
            if (class_exists(\Modules\FieldService\App\Models\Technician::class)) {
                $tech = \Modules\FieldService\App\Models\Technician::where('mobile', $registrationRequest->mobile)->first();
                if ($tech) {
                    $tech->status = \Modules\FieldService\App\Enums\TechnicianStatus::SUSPENDED;
                    $tech->is_online = false;
                    $tech->save();
                }
            }
        }

        return redirect()->back()->with('success', "درخواست «{$registrationRequest->name}» رد شد.");
    }
}

