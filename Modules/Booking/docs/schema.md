# DB Schema (DDL Summary)

> فایل‌های migration داخل `Database/Migrations` منبع حقیقت هستند.

## Tables

- booking_settings
- booking_categories
- booking_forms
- booking_services
- booking_service_providers
- booking_availability_rules
- booking_availability_exceptions
- appointments
- booking_slot_holds
- booking_payments
- booking_day_locks
- booking_slot_locks
- booking_audit_logs

## ERD (متنی)

booking_services 1---n booking_service_providers n---1 users (provider)
booking_services n---1 booking_categories
booking_services n---1 booking_forms (appointment_form)

appointments n---1 booking_services
appointments n---1 users (provider_user_id)
appointments n---1 clients
appointments 1---n booking_payments

booking_availability_rules: scoped by (GLOBAL|SERVICE|SERVICE_PROVIDER)
booking_availability_exceptions: scoped by (GLOBAL|SERVICE|SERVICE_PROVIDER) and local_date

booking_slot_holds: service_id + provider_user_id + slot range + expires

booking_day_locks / booking_slot_locks: used for pessimistic locking

booking_audit_logs: records sensitive actions
