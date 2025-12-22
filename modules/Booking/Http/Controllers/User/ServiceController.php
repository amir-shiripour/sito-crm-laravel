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
use Modules\Booking\Entities\BookingServiceProvider;


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

        if ($authUser) {
            $query->with(['serviceProviders' => function ($q) use ($authUser) {
                $q->where('provider_user_id', $authUser->id);
            }]);
        }

        if (! $isAdmin) {
            $query->where(function ($q) use ($authUser, $isProvider, $settings, $adminOwnerIds) {
                $q->whereNull('owner_user_id')
                    ->orWhereIn('owner_user_id', $adminOwnerIds);

                if ($isProvider && $settings->allow_role_service_creation && $authUser) {
                    $q->orWhere('owner_user_id', $authUser->id);
                }
            });
        }

        $services = $query->orderByDesc('id')->paginate(20);

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

        $categories = $this->categoriesForUser($authUser, $settings);
        $forms      = BookingForm::query()->orderBy('name')->get();

        $isAdminUser = $this->isAdminUser($authUser);
        $isProvider  = $this->userIsProvider($authUser, $settings);

        return view('booking::user.services.create', compact('categories', 'forms', 'settings', 'isAdminUser', 'isProvider'));
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();
        $settings = BookingSetting::current();

        if (! $this->canCreateService($authUser, $settings)) {
            abort(403);
        }

        $isAdminUser = $this->isAdminUser($authUser);

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
            'custom_schedule_enabled' => ['nullable', 'boolean'],
        ]);

        $this->ensureCategorySelectionAllowed($authUser, $settings, $data['category_id'] ?? null);

        // Provider اجازه تغییر این گزینه را ندارد
        if (! $isAdminUser) {
            $data['provider_can_customize'] = false;
        } else {
            $data['provider_can_customize'] = (bool)($data['provider_can_customize'] ?? false);
        }
        $data['custom_schedule_enabled'] = (bool)($data['custom_schedule_enabled'] ?? false);

        $data['discount_from'] = $data['discount_from'] ?: null;
        $data['discount_to']   = $data['discount_to']   ?: null;

        $data['owner_user_id'] = $authUser?->id;

        $service = BookingService::query()->create($data);

        // اگر سازنده Provider است، سرویس خودش برای خودش فعال شود
        if ($authUser && $this->userIsProvider($authUser, $settings)) {
            BookingServiceProvider::query()->updateOrCreate(
                ['service_id' => $service->id, 'provider_user_id' => $authUser->id],
                [
                    'is_active' => true,
                    'customization_enabled' => true,
                    'override_status_mode' => BookingServiceProvider::OVERRIDE_MODE_INHERIT,
                ]
            );
        }

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

        $categories = $this->categoriesForUser($authUser, $settings);
        $forms      = BookingForm::query()->orderBy('name')->get();

        $isAdminUser = $this->isAdminUser($authUser);
        $isProvider  = $this->userIsProvider($authUser, $settings);

        $isPublicService = $this->serviceIsPublic($service, $adminOwnerIds);
        $isOwnerService  = $authUser ? ((int)$service->owner_user_id === (int)$authUser->id) : false;

        $editingPublicAsProvider = (!$isAdminUser && $isProvider && $isPublicService && !$isOwnerService);
        $serviceProvider = null;

        if ($editingPublicAsProvider && $authUser) {
            $serviceProvider = BookingServiceProvider::query()
                ->where('service_id', $service->id)
                ->where('provider_user_id', $authUser->id)
                ->first();
        }

        return view('booking::user.services.edit', compact(
            'service',
            'categories',
            'forms',
            'settings',
            'isAdminUser',
            'isProvider',
            'isPublicService',
            'isOwnerService',
            'editingPublicAsProvider',
            'serviceProvider',
        ));
    }

    public function update(Request $request, BookingService $service)
    {
        $authUser = Auth::user();
        $settings = BookingSetting::current();
        $adminOwnerIds = $this->getAdminOwnerIds();

        if (! $this->canEditServiceForUser($authUser, $service, $adminOwnerIds, $settings)) {
            abort(403);
        }

        $isAdminUser = $this->isAdminUser($authUser);
        $isProvider  = $this->userIsProvider($authUser, $settings);

        $isPublicService = $this->serviceIsPublic($service, $adminOwnerIds);
        $isOwnerService  = $authUser ? ((int)$service->owner_user_id === (int)$authUser->id) : false;

        // -------------------------------------------------
        // حالت 1: Provider روی سرویس عمومی (override در pivot)
        // -------------------------------------------------
        if (! $isAdminUser && $isProvider && $isPublicService && ! $isOwnerService) {

            $data = $request->validate([
                // فقط فیلدهای مجاز برای Provider روی سرویس عمومی
                'base_price'     => ['required', 'numeric', 'min:0'],
                'discount_price' => ['nullable', 'numeric', 'min:0'],
                'discount_from'  => ['nullable', 'string'],
                'discount_to'    => ['nullable', 'string'],

                'category_id'         => ['nullable', 'integer', 'exists:booking_categories,id'],
                'appointment_form_id' => ['nullable', 'integer', 'exists:booking_forms,id'],

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
            ]);

            $data['discount_from'] = $data['discount_from'] ?: null;
            $data['discount_to']   = $data['discount_to']   ?: null;

            $sp = BookingServiceProvider::query()->firstOrNew([
                'service_id'       => $service->id,
                'provider_user_id' => $authUser->id,
            ]);

            if (! $sp->exists) {
                $sp->is_active = true; // اینجا منطقیه فعال باشه چون اجازه ورود به edit داشته
                $sp->customization_enabled = (bool) $service->provider_can_customize;
                $sp->override_status_mode = BookingServiceProvider::OVERRIDE_MODE_INHERIT;
            }

            // Override ها
            $sp->override_price_mode       = BookingServiceProvider::OVERRIDE_MODE_OVERRIDE;
            $sp->override_base_price       = $data['base_price'];
            $sp->override_discount_price   = $data['discount_price'] ?? null;
            $sp->override_discount_from    = $data['discount_from'] ?? null;
            $sp->override_discount_to      = $data['discount_to'] ?? null;

            $sp->override_category_id          = $data['category_id'] ?? null;
            $sp->override_appointment_form_id  = $data['appointment_form_id'] ?? null;

            $sp->override_online_booking_mode  = $data['online_booking_mode'];

            $sp->override_payment_mode         = $data['payment_mode'];
            $sp->override_payment_amount_type  = $data['payment_amount_type'] ?? null;
            $sp->override_payment_amount_value = $data['payment_amount_value'] ?? null;

            $this->ensureCategorySelectionAllowed($authUser, $settings, $sp->override_category_id);

            $sp->save();

            return redirect()
                ->route('user.booking.services.edit', $service)
                ->with('success', 'تنظیمات این سرویس برای شما ذخیره شد.');
        }

        // -------------------------------------------------
        // حالت 2: Admin یا Provider روی سرویس خودش (edit روی BookingService)
        // -------------------------------------------------

        $rules = [
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
            'custom_schedule_enabled' => ['nullable', 'boolean'],
        ];

        // فقط admin می‌تواند provider_can_customize را تغییر دهد
        if ($isAdminUser) {
            $rules['provider_can_customize'] = ['nullable', 'boolean'];
        }

        $data = $request->validate($rules);

        $this->ensureCategorySelectionAllowed($authUser, $settings, $data['category_id'] ?? null);

        $data['discount_from'] = $data['discount_from'] ?: null;
        $data['discount_to']   = $data['discount_to']   ?: null;

        if ($isAdminUser) {
            $data['provider_can_customize'] = (bool)($data['provider_can_customize'] ?? false);
        }
        if (array_key_exists('custom_schedule_enabled', $data)) {
            $data['custom_schedule_enabled'] = (bool) $data['custom_schedule_enabled'];
        }

        $service->fill($data)->save();

        return redirect()
            ->route('user.booking.services.edit', $service)
            ->with('success', 'سرویس با موفقیت بروزرسانی شد.');
    }

    public function toggleForMe(Request $request, BookingService $service)
    {
        $authUser = Auth::user();
        $settings = BookingSetting::current();
        $adminOwnerIds = $this->getAdminOwnerIds();

        if (! $authUser) {
            abort(403);
        }

        // فقط Providerها
        if (! $this->userIsProvider($authUser, $settings)) {
            abort(403);
        }

        // فقط سرویس‌های عمومی قابل فعال‌سازی برای Provider هستند
        if (! $this->serviceIsPublic($service, $adminOwnerIds)) {
            abort(403);
        }

        $sp = BookingServiceProvider::query()->firstOrNew([
            'service_id'        => $service->id,
            'provider_user_id'  => $authUser->id,
        ]);

        // پیشفرض‌ها اگر رکورد تازه ساخته می‌شود
        if (! $sp->exists) {
            $sp->customization_enabled = (bool) $service->provider_can_customize;
            $sp->override_status_mode = BookingServiceProvider::OVERRIDE_MODE_INHERIT;
        }

        $sp->is_active = ! (bool) $sp->is_active;
        $sp->save();

        return redirect()
            ->route('user.booking.services.index')
            ->with('success', $sp->is_active ? 'سرویس برای شما فعال شد.' : 'سرویس برای شما غیرفعال شد.');
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

        // سرویس عمومی و قابل شخصی‌سازی توسط Provider (به شرط فعال بودن برای همین Provider)
        if ($this->serviceIsPublic($service, $adminOwnerIds) && $service->provider_can_customize) {
            $sp = BookingServiceProvider::query()
                ->where('service_id', $service->id)
                ->where('provider_user_id', $user->id)
                ->first();

            if ($sp && $sp->is_active) {
                return true;
            }
        }


        return false;
    }

    protected function categoriesForUser(?User $user, BookingSetting $settings)
    {
        $query = BookingCategory::query()->orderBy('name');

        if ($settings->service_category_selection_scope === 'OWN'
            && $user
            && ! $user->can('booking.categories.manage')
            && ! $user->hasRole('super-admin')) {
            $query->where('creator_id', $user->id);
        }

        return $query->get();
    }

    protected function ensureCategorySelectionAllowed(?User $user, BookingSetting $settings, ?int $categoryId): void
    {
        if (! $categoryId) {
            return;
        }

        if (! $user) {
            abort(403);
        }

        if ($settings->service_category_selection_scope !== 'OWN') {
            return;
        }

        if ($user->can('booking.categories.manage') || $user->hasRole('super-admin')) {
            return;
        }

        $ownsCategory = BookingCategory::query()
            ->where('id', $categoryId)
            ->where('creator_id', $user->id)
            ->exists();

        if (! $ownsCategory) {
            abort(403);
        }
    }
}
