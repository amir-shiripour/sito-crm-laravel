<?php

namespace Modules\Booking\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingForm;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Spatie\Permission\Models\Role;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $authUser  = Auth::user();
        $settings  = BookingSetting::current();

        $isAdmin   = $this->isAdminUser($authUser);
        $isProvider = $this->userIsProvider($authUser, $settings);
        $adminOwnerIds = $this->getAdminOwnerIds();

        $query = BookingService::query()
            ->with(['category', 'appointmentForm']);

        if (! $isAdmin) {
            // فیلتر کردن بر اساس قواعد:
            // - سرویس‌های عمومی (owner_user_id null یا متعلق به admin/super-admin)
            // - + سرویس‌های خود کاربر (اگر Provider است و allow_role_service_creation = true)
            $query->where(function ($q) use ($authUser, $isProvider, $settings, $adminOwnerIds) {
                // سرویس‌های عمومی
                $q->whereNull('owner_user_id')
                    ->orWhereIn('owner_user_id', $adminOwnerIds);

                // سرویس‌های خود ارائه‌دهنده (در صورت فعال بودن اجازهٔ ایجاد سرویس)
                if ($isProvider && $settings->allow_role_service_creation && $authUser) {
                    $q->orWhere('owner_user_id', $authUser->id);
                }
            });
        }

        $services = $query
            ->orderByDesc('id')
            ->paginate(20);

        // محاسبه اینکه کدام سرویس‌ها برای این کاربر قابل ویرایش هستند
        $editableServiceIds = [];
        foreach ($services as $srv) {
            if ($this->canEditServiceForUser($authUser, $srv, $adminOwnerIds, $settings)) {
                $editableServiceIds[] = $srv->id;
            }
        }

        return view('booking::user.services.index', [
            'services'           => $services,
            'settings'           => $settings,
            'isAdminUser'        => $isAdmin,
            'isProvider'         => $isProvider,
            'adminOwnerIds'      => $adminOwnerIds,
            'editableServiceIds' => $editableServiceIds,
        ]);
    }

    public function create()
    {
        $authUser = Auth::user();
        $settings = BookingSetting::current();

        if (! $this->canCreateService($authUser, $settings)) {
            abort(403);
        }

        $categories = BookingCategory::query()->orderBy('name')->get();
        $forms      = BookingForm::query()->orderBy('name')->get();

        return view('booking::user.services.create', compact('categories', 'forms'));
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();
        $settings = BookingSetting::current();

        if (! $this->canCreateService($authUser, $settings)) {
            abort(403);
        }

        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in([BookingService::STATUS_ACTIVE, BookingService::STATUS_INACTIVE])],

            'base_price'     => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'discount_from'  => ['nullable', 'string'],
            'discount_to'    => ['nullable', 'string'],

            'category_id'         => ['nullable', 'integer', 'exists:booking_categories,id'],
            'online_booking_mode' => ['required', Rule::in([
                BookingService::ONLINE_MODE_INHERIT,
                BookingService::ONLINE_MODE_FORCE_ON,
                BookingService::ONLINE_MODE_FORCE_OFF,
            ])],

            'payment_mode'       => ['required', Rule::in([
                BookingService::PAYMENT_MODE_NONE,
                BookingService::PAYMENT_MODE_OPTIONAL,
                BookingService::PAYMENT_MODE_REQUIRED,
            ])],
            'payment_amount_type'  => ['nullable', Rule::in([
                BookingService::PAYMENT_AMOUNT_FULL,
                BookingService::PAYMENT_AMOUNT_DEPOSIT,
                BookingService::PAYMENT_AMOUNT_FIXED,
            ])],
            'payment_amount_value' => ['nullable', 'numeric', 'min:0'],

            'appointment_form_id'   => ['nullable', 'integer', 'exists:booking_forms,id'],
            'provider_can_customize'=> ['nullable', 'boolean'],
        ]);

        $data['provider_can_customize'] = (bool)($data['provider_can_customize'] ?? false);
        $data['discount_from'] = $data['discount_from'] ?: null;
        $data['discount_to']   = $data['discount_to']   ?: null;

        // مالک سرویس همیشه کاربری است که سرویس را ایجاد می‌کند
        $data['owner_user_id'] = $authUser?->id;

        BookingService::query()->create($data);

        return redirect()
            ->route('user.booking.services.index')
            ->with('success', 'سرویس با موفقیت ثبت شد.');
    }

    public function edit(BookingService $service)
    {
        $authUser = Auth::user();
        $settings = BookingSetting::current();
        $adminOwnerIds = $this->getAdminOwnerIds();

        if (! $this->canEditServiceForUser($authUser, $service, $adminOwnerIds, $settings)) {
            abort(403);
        }

        $categories = BookingCategory::query()->orderBy('name')->get();
        $forms      = BookingForm::query()->orderBy('name')->get();

        return view('booking::user.services.edit', compact('service', 'categories', 'forms'));
    }

    public function update(Request $request, BookingService $service)
    {
        $authUser = Auth::user();
        $settings = BookingSetting::current();
        $adminOwnerIds = $this->getAdminOwnerIds();

        if (! $this->canEditServiceForUser($authUser, $service, $adminOwnerIds, $settings)) {
            abort(403);
        }

        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in([BookingService::STATUS_ACTIVE, BookingService::STATUS_INACTIVE])],

            'base_price'     => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'discount_from'  => ['nullable', 'string'],
            'discount_to'    => ['nullable', 'string'],

            'category_id'         => ['nullable', 'integer', 'exists:booking_categories,id'],
            'online_booking_mode' => ['required', Rule::in([
                BookingService::ONLINE_MODE_INHERIT,
                BookingService::ONLINE_MODE_FORCE_ON,
                BookingService::ONLINE_MODE_FORCE_OFF,
            ])],

            'payment_mode'       => ['required', Rule::in([
                BookingService::PAYMENT_MODE_NONE,
                BookingService::PAYMENT_MODE_OPTIONAL,
                BookingService::PAYMENT_MODE_REQUIRED,
            ])],
            'payment_amount_type'  => ['nullable', Rule::in([
                BookingService::PAYMENT_AMOUNT_FULL,
                BookingService::PAYMENT_AMOUNT_DEPOSIT,
                BookingService::PAYMENT_AMOUNT_FIXED,
            ])],
            'payment_amount_value' => ['nullable', 'numeric', 'min:0'],

            'appointment_form_id'   => ['nullable', 'integer', 'exists:booking_forms,id'],
            'provider_can_customize'=> ['nullable', 'boolean'],
        ]);

        $data['provider_can_customize'] = (bool)($data['provider_can_customize'] ?? false);
        $data['discount_from'] = $data['discount_from'] ?: null;
        $data['discount_to']   = $data['discount_to']   ?: null;

        $service->fill($data)->save();

        return redirect()
            ->route('user.booking.services.edit', $service)
            ->with('success', 'سرویس با موفقیت بروزرسانی شد.');
    }

    // --------------------------------------------------
    // Helperهای دسترسی
    // --------------------------------------------------

    /**
     * آیا کاربر ادمین/سوپرادمین/مدیر نوبت‌دهی است؟
     */
    protected function isAdminUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        // اگر در جای دیگری برای مدیریت کلی نوبت‌دهی از این پرمیشن‌ها استفاده می‌کنی
        if ($user->can('booking.manage') || $user->can('booking.services.manage')) {
            return true;
        }

        return false;
    }

    /**
     * نقش‌های Provider از روی BookingSetting
     */
    protected function getProviderRoleIds(BookingSetting $settings): array
    {
        return array_map('intval', (array)($settings->allowed_roles ?? []));
    }

    /**
     * آیا این کاربر براساس تنظیمات، Provider است؟
     */
    protected function userIsProvider(?User $user, BookingSetting $settings): bool
    {
        if (! $user) {
            return false;
        }

        $providerRoleIds = $this->getProviderRoleIds($settings);
        if (empty($providerRoleIds)) {
            return false;
        }

        $userRoleIds = $user->roles()->pluck('id')->map(fn ($v) => (int)$v)->all();

        return count(array_intersect($providerRoleIds, $userRoleIds)) > 0;
    }

    /**
     * لیست user_id هایی که نقش admin / super-admin دارند
     * (همین‌ها مالک سرویس‌های عمومی محسوب می‌شوند)
     */
    protected function getAdminOwnerIds(): array
    {
        $roleIds = Role::query()
            ->whereIn('name', ['super-admin', 'admin'])
            ->pluck('id')
            ->all();

        if (empty($roleIds)) {
            return [];
        }

        return DB::table('model_has_roles')
            ->whereIn('role_id', $roleIds)
            ->where('model_type', User::class)
            ->pluck('model_id')
            ->map(fn ($v) => (int)$v)
            ->all();
    }

    /**
     * سرویس عمومی است؟ (owner_user_id خالی یا متعلق به admin/super-admin)
     */
    protected function serviceIsPublic(BookingService $service, array $adminOwnerIds): bool
    {
        if ($service->owner_user_id === null) {
            return true;
        }

        return in_array((int)$service->owner_user_id, $adminOwnerIds, true);
    }

    /**
     * آیا این کاربر حق ایجاد سرویس جدید دارد؟
     */
    protected function canCreateService(?User $user, BookingSetting $settings): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isAdminUser($user)) {
            return true;
        }

        // باید Provider باشد
        if (! $this->userIsProvider($user, $settings)) {
            return false;
        }

        // تنظیمات اجازه ساخت سرویس را بدهند
        if (! $settings->allow_role_service_creation) {
            return false;
        }

        // و پرمیشن سطحی داشته باشد (برای هم‌خوانی با Spatie)
        if (! $user->can('booking.services.create')) {
            return false;
        }

        return true;
    }

    /**
     * آیا این کاربر می‌تواند این سرویس خاص را ویرایش کند؟
     *
     * قواعد:
     *  - ادمین / سوپرادمین → همیشه می‌تواند.
     *  - Provider:
     *      - اگر allow_role_service_creation = 1 و owner_user_id == user_id → می‌تواند.
     *      - اگر سرویس عمومی است و provider_can_customize = 1 → می‌تواند (حتی اگر اجازه ساخت فعال نباشد).
     */
    protected function canEditServiceForUser(?User $user, BookingService $service, array $adminOwnerIds, BookingSetting $settings): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isAdminUser($user)) {
            return true;
        }

        $isProvider = $this->userIsProvider($user, $settings);
        if (! $isProvider) {
            return false;
        }

        // مالک سرویس (در صورت فعال بودن امکان ساخت)
        if ($settings->allow_role_service_creation && (int)$service->owner_user_id === (int)$user->id) {
            return true;
        }

        // سرویس عمومی و قابل شخصی‌سازی توسط Provider
        if ($this->serviceIsPublic($service, $adminOwnerIds) && $service->provider_can_customize) {
            return true;
        }

        return false;
    }
}
