# UX Flow & Screens (پیشنهادی + وضعیت پیاده‌سازی)

> این فایل برای تیم UI است. نسخه فعلی ماژول فقط **UI حداقلی** دارد (dashboard, services, appointments, settings) و فلو کامل آنلاین/اپراتوری بهتر است با SPA یا Livewire تکمیل شود.

## 1) Admin Dashboard (Booking KPI)
**هدف:** نمایش KPIها و مسیر سریع برای مدیریت policyها

- KPI:
  - total appointments
  - confirmed
  - cancellations
  - no-show
  - revenue_paid
  - online vs operator (created_by_type)
- لینک‌های سریع:
  - Services
  - Categories
  - Forms
  - Availability Rules/Exceptions
  - Reports

**وضعیت فعلی:** Dashboard حداقلی در `booking::user.dashboard`.

---

## 2) Service Editor
بخش‌ها:
1) اطلاعات پایه: نام، وضعیت، قیمت، تخفیف (window)
2) دسته‌بندی و فرم
3) OnlineBookingMode و PaymentMode
4) Policy زمان‌بندی و ظرفیت‌ها:
   - نمایش inheritance هر فیلد (Global/Service/Provider/Exception)
   - CRUD Ruleها (weekday) و Exceptions (date)
5) Attach providers:
   - انتخاب چندتایی providerها (booking_service_providers)
6) Provider customization flags:
   - provider_can_customize (روی سرویس)
   - customization_enabled (روی هر service_provider)

**وضعیت فعلی:** Create/Edit سرویس حداقلی دارد (بدون policy editor و attach providers UI).

---

## 3) Provider Customization UI
- اگر customization_enabled=true:
  - override_price_mode + prices
  - override_status_mode + status
  - override_online_booking_mode
  - (زمان‌بندی provider) از طریق AvailabilityRule/Exception با scope=SERVICE_PROVIDER

**وضعیت فعلی:** API برای updateServiceProvider وجود دارد ولی UI هنوز حداقلی است.

---

## 4) Operator Booking Flow
Flow پیشنهادی:
1) انتخاب سرویس
2) انتخاب Provider (از providerهای attach شده)
3) انتخاب یا ساخت Client (search/autocomplete)
4) انتخاب تاریخ و slot (get slots)
5) نمایش remaining_capacity و conflict warning
6) تکمیل appointment form (اگر سرویس فرم دارد)
7) Confirm

**وضعیت فعلی:** صفحه ثبت نوبت اپراتوری حداقلی (بدون slot picker و بدون فرم).

---

## 5) Online Booking Flow
Flow پیشنهادی:
1) service -> provider
2) date -> slot list
3) start hold (TTL)
4) login/signup (یا OTP)
5) collect required client fields (client_profile_required_fields)
6) appointment form
7) payment (optional/required)
8) confirm

**وضعیت فعلی:** صفحات عمومی حداقلی موجود است. فلو کامل از طریق API باید ساخته شود.
