# Background Jobs / Commands

این ماژول سه command اصلی دارد (می‌توانید آن‌ها را در cron یا scheduler قرار دهید):

## 1) booking:cleanup-holds
- پاکسازی `booking_slot_holds` که `expires_at_utc < now()`
- هدف: جلوگیری از اشغال بی‌دلیل ظرفیت و پاکیزگی دیتابیس

## 2) booking:handle-payment-timeouts
- پیدا کردن Appointmentهای `PENDING_PAYMENT` که از `created_at` بیشتر از `payment_timeout_minutes` گذشته
- تغییر status به `CANCELED_BY_ADMIN`
- Paymentهای pending مرتبط -> `CANCELED`

## 3) booking:dispatch-reminders
- Reminder provider (IN_APP) از طریق ماژول Reminders
- Reminder client (SMS) از طریق ماژول SmsMessage

> نکته: برای Reminder client داخل entity Reminder، نیاز به تغییرات معماری در Reminders module دارید.
