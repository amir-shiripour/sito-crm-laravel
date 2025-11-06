# Sito CRM (Laravel) – راهنمای سریع

این مخزن شامل پروژه **sito-crm-laravel** است؛ یک CRM ماژولار مبتنی بر Laravel 10 با استفاده از Jetstream، Livewire و TailwindCSS. برای شروع سریع، مراحل زیر را دنبال کنید و برای جزئیات کامل به [docs/PROJECT_GUIDE.md](docs/PROJECT_GUIDE.md) مراجعه نمایید.

## شروع سریع

1. `cp .env.example .env`
2. تنظیم مقادیر اتصال پایگاه‌داده و درایور صف/کش در `.env`
3. `composer install`
4. `php artisan key:generate`
5. `npm install`
6. `php artisan migrate --seed`
7. `npm run dev`
8. `php artisan serve`

> **نکته:** راهنمای کامل توسعه، ساختار پروژه، الگوهای معماری و چک‌لیست تیمی در فایل [docs/PROJECT_GUIDE.md](docs/PROJECT_GUIDE.md) موجود است.

## منابع مفید

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire v3 Docs](https://livewire.laravel.com/)
- [TailwindCSS Docs](https://tailwindcss.com/docs)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [nWidart Modules Docs](https://nwidart.com/laravel-modules/v11/introduction)

## مجوز

این پروژه تحت مجوز MIT عرضه می‌شود. برای اطلاعات بیشتر فایل `LICENSE` (در صورت وجود) را بررسی کنید.
