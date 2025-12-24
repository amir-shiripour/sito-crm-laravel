<?php

namespace Modules\Booking\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingForm;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\AuditLogger;

class ServiceController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $q = BookingService::query()
            ->with(['category', 'appointmentForm', 'serviceProviders.provider']);

        if ($search = $request->query('q')) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }

        if ($categoryId = $request->query('category_id')) {
            $q->where('category_id', (int) $categoryId);
        }

        // Scope rule: if user cannot manage all and allow_role_service_creation is enabled, limit to own services
        $settings = BookingSetting::current();
        $allowedRoles = (array) ($settings->allowed_roles ?? []);

        $userRoleNames = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->all() : [];

        $isScopedRole = $settings->allow_role_service_creation
            && !empty($allowedRoles)
            && count(array_intersect($allowedRoles, $userRoleNames)) > 0;

        $canManageAll = $user->can('booking.services.manage') || $user->can('booking.manage') || $user->hasRole('super-admin');

        if ($isScopedRole && !$canManageAll) {
            $q->where('owner_user_id', $user->id);
        }

        return response()->json([
            'data' => $q->orderByDesc('id')->paginate((int) ($request->query('per_page', 20))),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([BookingService::STATUS_ACTIVE, BookingService::STATUS_INACTIVE])],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'discount_from' => ['nullable', 'date'],
            'discount_to' => ['nullable', 'date'],

            'category_id' => ['nullable', 'integer'],
            'online_booking_mode' => ['nullable', Rule::in([BookingService::ONLINE_MODE_INHERIT, BookingService::ONLINE_MODE_FORCE_ON, BookingService::ONLINE_MODE_FORCE_OFF])],

            'payment_mode' => ['nullable', Rule::in([BookingService::PAYMENT_MODE_NONE, BookingService::PAYMENT_MODE_OPTIONAL, BookingService::PAYMENT_MODE_REQUIRED])],
            'payment_amount_type' => ['nullable', Rule::in([BookingService::PAYMENT_AMOUNT_FULL, BookingService::PAYMENT_AMOUNT_DEPOSIT, BookingService::PAYMENT_AMOUNT_FIXED])],
            'payment_amount_value' => ['nullable', 'numeric', 'min:0'],

            'appointment_form_id' => ['nullable', 'integer'],
            'client_profile_required_fields' => ['nullable', 'array'],
            'client_profile_required_fields.*' => ['string'],

            'provider_can_customize' => ['nullable', 'boolean'],
            'custom_schedule_enabled' => ['nullable', 'boolean'],

            // optional attach providers
            'provider_user_ids' => ['nullable', 'array'],
            'provider_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Enforce category/form ownership if needed
        $settings = BookingSetting::current();

        if (!empty($data['category_id']) && $settings->service_category_selection_scope === 'OWN' && !$user->can('booking.categories.manage') && !$user->hasRole('super-admin')) {
            $ok = BookingCategory::query()->where('id', (int) $data['category_id'])->where('creator_id', $user->id)->exists();
            if (!$ok) {
                return response()->json(['message' => 'You cannot select this category.'], 403);
            }
        }

        if (!empty($data['appointment_form_id']) && $settings->service_form_selection_scope === 'OWN' && !$user->can('booking.forms.manage') && !$user->hasRole('super-admin')) {
            $ok = BookingForm::query()->where('id', (int) $data['appointment_form_id'])->where('creator_id', $user->id)->exists();
            if (!$ok) {
                return response()->json(['message' => 'You cannot select this form.'], 403);
            }
        }

        // Apply role-based service ownership
        $allowedRoles = (array) ($settings->allowed_roles ?? []);
        $userRoleNames = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->all() : [];

        $isScopedRole = $settings->allow_role_service_creation
            && !empty($allowedRoles)
            && count(array_intersect($allowedRoles, $userRoleNames)) > 0;

        $canManageAll = $user->can('booking.services.manage') || $user->can('booking.manage') || $user->hasRole('super-admin');

        $data['owner_user_id'] = ($isScopedRole && !$canManageAll) ? $user->id : null;

        $providerIds = $data['provider_user_ids'] ?? [];
        unset($data['provider_user_ids']);

        if (array_key_exists('custom_schedule_enabled', $data)) {
            $data['custom_schedule_enabled'] = (bool) $data['custom_schedule_enabled'];
        }

        $service = BookingService::query()->create($data);

        if (!empty($providerIds)) {
            foreach ($providerIds as $pid) {
                BookingServiceProvider::query()->updateOrCreate([
                    'service_id' => $service->id,
                    'provider_user_id' => (int) $pid,
                ], [
                    'is_active' => true,
                    'customization_enabled' => false,
                ]);
            }
        }

        $this->audit->log('SERVICE_CREATED', 'booking_services', $service->id, null, $service->toArray());

        return response()->json(['data' => $service->fresh(['category', 'appointmentForm', 'serviceProviders.provider'])], 201);
    }

    public function update(Request $request, BookingService $service)
    {
        $user = $request->user();

        // Scope rule check: if user is scoped role, only update own service
        $settings = BookingSetting::current();
        $allowedRoles = (array) ($settings->allowed_roles ?? []);
        $userRoleNames = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->all() : [];

        $isScopedRole = $settings->allow_role_service_creation
            && !empty($allowedRoles)
            && count(array_intersect($allowedRoles, $userRoleNames)) > 0;

        $canManageAll = $user->can('booking.services.manage') || $user->can('booking.manage') || $user->hasRole('super-admin');

        if ($isScopedRole && !$canManageAll && (int) $service->owner_user_id !== (int) $user->id) {
            return response()->json(['message' => 'Access denied (scoped).'], 403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in([BookingService::STATUS_ACTIVE, BookingService::STATUS_INACTIVE])],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'discount_from' => ['nullable', 'date'],
            'discount_to' => ['nullable', 'date'],

            'category_id' => ['nullable', 'integer'],
            'online_booking_mode' => ['sometimes', Rule::in([BookingService::ONLINE_MODE_INHERIT, BookingService::ONLINE_MODE_FORCE_ON, BookingService::ONLINE_MODE_FORCE_OFF])],

            'payment_mode' => ['sometimes', Rule::in([BookingService::PAYMENT_MODE_NONE, BookingService::PAYMENT_MODE_OPTIONAL, BookingService::PAYMENT_MODE_REQUIRED])],
            'payment_amount_type' => ['nullable', Rule::in([BookingService::PAYMENT_AMOUNT_FULL, BookingService::PAYMENT_AMOUNT_DEPOSIT, BookingService::PAYMENT_AMOUNT_FIXED])],
            'payment_amount_value' => ['nullable', 'numeric', 'min:0'],

            'appointment_form_id' => ['nullable', 'integer'],
            'client_profile_required_fields' => ['nullable', 'array'],
            'client_profile_required_fields.*' => ['string'],

            'provider_can_customize' => ['sometimes', 'boolean'],
            'custom_schedule_enabled' => ['sometimes', 'boolean'],
        ]);

        $before = $service->toArray();
        if (array_key_exists('custom_schedule_enabled', $data)) {
            $data['custom_schedule_enabled'] = (bool) $data['custom_schedule_enabled'];
        }
        $service->fill($data);
        $service->save();
        $this->audit->log('SERVICE_UPDATED', 'booking_services', $service->id, $before, $service->toArray());

        return response()->json(['data' => $service->fresh(['category', 'appointmentForm', 'serviceProviders.provider'])]);
    }

    public function destroy(Request $request, BookingService $service)
    {
        $user = $request->user();

        // Scope rule: only owner can delete if scoped
        $settings = BookingSetting::current();
        $allowedRoles = (array) ($settings->allowed_roles ?? []);
        $userRoleNames = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->all() : [];

        $isScopedRole = $settings->allow_role_service_creation
            && !empty($allowedRoles)
            && count(array_intersect($allowedRoles, $userRoleNames)) > 0;

        $canManageAll = $user->can('booking.services.manage') || $user->can('booking.manage') || $user->hasRole('super-admin');

        if ($isScopedRole && !$canManageAll && (int) $service->owner_user_id !== (int) $user->id) {
            return response()->json(['message' => 'Access denied (scoped).'], 403);
        }

        $before = $service->toArray();
        $service->delete();
        $this->audit->log('SERVICE_DELETED', 'booking_services', $service->id, $before, null);
        return response()->json(['ok' => true]);
    }

    public function attachProviders(Request $request, BookingService $service)
    {
        $data = $request->validate([
            'provider_user_ids' => ['required', 'array'],
            'provider_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['provider_user_ids'])));

        foreach ($ids as $pid) {
            BookingServiceProvider::query()->updateOrCreate([
                'service_id' => $service->id,
                'provider_user_id' => $pid,
            ], [
                'is_active' => true,
            ]);
        }

        $this->audit->log('SERVICE_PROVIDERS_ATTACHED', 'booking_services', $service->id, null, ['provider_user_ids' => $ids]);
        return response()->json(['data' => $service->fresh(['serviceProviders.provider'])]);
    }

    public function updateServiceProvider(Request $request, BookingServiceProvider $serviceProvider)
    {
        $data = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'customization_enabled' => ['sometimes', 'boolean'],

            'override_price_mode' => ['sometimes', Rule::in([BookingServiceProvider::OVERRIDE_MODE_INHERIT, BookingServiceProvider::OVERRIDE_MODE_OVERRIDE])],
            'override_base_price' => ['nullable', 'numeric', 'min:0'],
            'override_discount_price' => ['nullable', 'numeric', 'min:0'],
            'override_discount_from' => ['nullable', 'date'],
            'override_discount_to' => ['nullable', 'date'],

            'override_online_booking_mode' => ['nullable', Rule::in([BookingService::ONLINE_MODE_INHERIT, BookingService::ONLINE_MODE_FORCE_ON, BookingService::ONLINE_MODE_FORCE_OFF])],

            'override_status_mode' => ['sometimes', Rule::in([BookingServiceProvider::OVERRIDE_MODE_INHERIT, BookingServiceProvider::OVERRIDE_MODE_OVERRIDE])],
            'override_status' => ['nullable', Rule::in([BookingService::STATUS_ACTIVE, BookingService::STATUS_INACTIVE])],
        ]);

        // Enforce that customization_enabled can only be true if service allows it
        $service = $serviceProvider->service;
        if ($service && !$service->provider_can_customize) {
            $data['customization_enabled'] = false;
        }

        $before = $serviceProvider->toArray();
        $serviceProvider->fill($data);
        $serviceProvider->save();
        $this->audit->log('SERVICE_PROVIDER_UPDATED', 'booking_service_providers', $serviceProvider->id, $before, $serviceProvider->toArray());

        return response()->json(['data' => $serviceProvider->fresh(['service', 'provider'])]);
    }
}
