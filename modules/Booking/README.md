# Booking Module (Appointments / Online Booking)

این ماژول برای CRM شما طراحی شده و شامل موارد زیر است:

- تنظیمات کلی (BookingSetting)
- دسته‌بندی‌ها (BookingCategory)
- فرم‌ساز (BookingForm) برای Appointment Form
- سرویس‌ها (BookingService) + اتصال Providerها (BookingServiceProvider)
- قوانین زمان‌بندی (BookingAvailabilityRule) و استثناها (BookingAvailabilityException)
- موتور تولید اسلات‌ها (BookingEngine) با merge policy طبق اولویت:
  - Global -> Service -> ServiceProvider -> DateExceptions
- جلوگیری از overbooking:
  - SlotHold با TTL برای رزرو آنلاین
  - قفل‌های DB سطح روز و اسلات (booking_day_locks / booking_slot_locks) برای رقابت همزمان
- نوبت‌ها (Appointment) + پرداخت‌ها (BookingPayment)
- گزارشات (ReportController)
- لاگ اودیت (BookingAuditLog)

> **Timezone**
> - Storage: UTC
> - Schedule rules: Asia/Tehran (local times)
> - Display default: Asia/Tehran

---

## نصب

1) پوشه ماژول را در `Modules/Booking` قرار دهید (ساختار: `Modules/Booking/Booking/...`).

2) در پروژه:
```bash
php artisan booking:install
php artisan migrate
php artisan db:seed --class="Modules\Booking\Database\Seeders\BookingSeeder"
```

3) (اختیاری) اضافه کردن کران‌جاب‌ها:

```bash
# پاکسازی hold های منقضی
php artisan booking:cleanup-holds

# کنسل خودکار PENDING_PAYMENT های منقضی
php artisan booking:handle-payment-timeouts

# ارسال یادآوری‌ها
php artisan booking:dispatch-reminders
```

---

## نکات مهم طراحی

### 1) اولویت merge policy
- DateException (GLOBAL/SERVICE/PROVIDER) بالاترین اولویت را دارد.
- سپس ProviderRule
- سپس ServiceRule
- سپس GlobalRule
- و نهایتاً Config defaults

### 2) ظرفیت دقیق (بدون ابهام)
هر دو ظرفیت همزمان اعمال می‌شوند:
- `capacity_per_slot`: سقف رزرو در یک اسلات (مثلاً برای گروهی)
- `capacity_per_day`: سقف رزرو در یک روز

### 3) Online booking
- آنلاین با `SlotHold` شروع می‌شود تا از overbooking جلوگیری شود.
- در confirm نهایی، hold مصرف می‌شود و Appointment ثبت می‌گردد.
- در confirm نهایی، دوباره ظرفیت در یک تراکنش چک می‌شود.

---

## محدودیت فعلی (نیاز به تصمیم/هماهنگی)

**Reminder برای CLIENT** طبق spec شما باید در entity Reminder ذخیره شود،
اما در نسخه فعلی ماژول Reminders، جدول `reminders.user_id` اجباری است و به `users` وصل می‌شود.
پس در این پیاده‌سازی:
- Reminderهای Provider در جدول Reminders ذخیره می‌شوند (IN_APP).
- Reminderهای CLIENT به صورت SMS زمان‌بندی‌شده در جدول SmsMessage ذخیره می‌شوند.

اگر می‌خواهید دقیقاً مطابق spec، Reminder برای CLIENT هم داخل Reminders ذخیره شود، باید یکی از این کارها انجام شود:
- تغییر schema Reminders (nullable user_id + target_type/target_id) یا
- ایجاد “client users” در جدول users و نگاشت با clients

قبل از اعمال این تغییر، لطفاً نسخه دقیق ماژول Reminders و تصمیم معماری شما را مشخص کنید.

---

## مسیرهای API

نمونه‌ها را در `docs/api.md` ببینید.
