# RBAC (نقش‌ها و مجوزها)

این ماژول از **Permission-based** دسترسی استفاده می‌کند (Laravel `can()` / roles).

## نقش‌های پیشنهادی (Spec)

- SUPER_ADMIN
- CRM_ADMIN
- PROVIDER_ROLE
- OPERATOR
- CLIENT (رزرو آنلاین)

> در پروژه شما نام Roleها ممکن است متفاوت باشد. در این ماژول فقط از permission strings استفاده می‌کنیم و نقش‌ها را به پروژه واگذار می‌کنیم.

---

## Permissionهای استفاده‌شده در UI/User routes

- `booking.view`
- `booking.manage` (اختیاری برای super admin)
- `booking.settings.manage`

- `booking.services.view`
- `booking.services.create`
- `booking.services.edit`
- `booking.services.delete`
- `booking.services.manage` (برای مدیریت کامل سرویس‌ها و bypass scope)

- `booking.appointments.view`
- `booking.appointments.create`
- `booking.appointments.edit` (در نسخه فعلی UI استفاده نشده)

- `booking.categories.manage` (برای bypass scope تنظیمات category_management_scope)
- `booking.forms.manage` (برای bypass scope تنظیمات form_management_scope)

---

## Scope Rules (طبق Spec)

### 1) category_management_scope
در `booking_settings.category_management_scope`:
- `ALL`: همه دسته‌ها قابل مشاهده/مدیریت است.
- `OWN`: کاربر فقط دسته‌هایی که `creator_id = user_id` دارد را می‌بیند/مدیریت می‌کند
  مگر اینکه permission `booking.categories.manage` یا super-admin داشته باشد.

### 2) form_management_scope
مشابه بالا برای فرم‌ها.

### 3) service_category_selection_scope و service_form_selection_scope
اگر روی `OWN` باشد و کاربر `booking.categories.manage` یا `booking.forms.manage` نداشته باشد،
در زمان ایجاد/ویرایش سرویس فقط می‌تواند دسته/فرم‌هایی که خودش ساخته را انتخاب کند.

### 4) allow_role_service_creation + allowed_roles
اگر `allow_role_service_creation = true` و نقش کاربر داخل `allowed_roles` باشد:
- سرویس‌های ساخته شده توسط این کاربر `owner_user_id = user_id` می‌گیرند
- اگر کاربر permission `booking.services.manage` نداشته باشد، در API فقط سرویس‌های خودش را می‌بیند/ویرایش/حذف می‌کند

---

## مثال‌ها

### مثال ۱: اپراتور
- role: OPERATOR
- permissions:
  - booking.view
  - booking.appointments.view
  - booking.appointments.create
  - booking.services.view

نتیجه:
- می‌تواند نوبت ثبت کند، لیست نوبت‌ها را ببیند، سرویس‌ها را مشاهده کند.
- به تنظیمات کلی یا ساخت سرویس دسترسی ندارد.

### مثال ۲: provider با اجازه ساخت سرویس
- role: PROVIDER_ROLE
- settings: allow_role_service_creation=true و allowed_roles شامل PROVIDER_ROLE
- permissions: booking.services.create, booking.services.view, booking.services.edit

نتیجه:
- می‌تواند سرویس بسازد و فقط سرویس‌های خودش را مدیریت کند (Scoped).
- برای دیدن/مدیریت سرویس‌های دیگر نیاز به booking.services.manage دارد.

---

## Seeder پیشنهادی
اگر پروژه شما از Spatie Permission استفاده می‌کند، seeder `BookingPermissionsSeeder` را می‌توانید اجرا کنید.
