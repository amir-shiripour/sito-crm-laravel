diff --git a/README.md b/README.md
index 1a4c26ba3297a45e4e2993dea65169ce318e9a6f..ee5c933b3beb62b4f0876231a298fb14094c98a1 100644
--- a/README.md
+++ b/README.md
@@ -1,66 +1,28 @@
-<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
+# Sito CRM (Laravel) – راهنمای سریع

-<p align="center">
-<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
-</p>
+این مخزن شامل پروژه **sito-crm-laravel** است؛ یک CRM ماژولار مبتنی بر Laravel 10 با استفاده از Jetstream، Livewire و TailwindCSS. برای شروع سریع، مراحل زیر را دنبال کنید و برای جزئیات کامل به [docs/PROJECT_GUIDE.md](docs/PROJECT_GUIDE.md) مراجعه نمایید.

-## About Laravel
+## شروع سریع

-Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:
+1. `cp .env.example .env`
+2. تنظیم مقادیر اتصال پایگاه‌داده و درایور صف/کش در `.env`
+3. `composer install`
+4. `php artisan key:generate`
+5. `npm install`
+6. `php artisan migrate --seed`
+7. `npm run dev`
+8. `php artisan serve`

-- [Simple, fast routing engine](https://laravel.com/docs/routing).
-- [Powerful dependency injection container](https://laravel.com/docs/container).
-- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-- [Robust background job processing](https://laravel.com/docs/queues).
-- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).
+> **نکته:** راهنمای کامل توسعه، ساختار پروژه، الگوهای معماری و چک‌لیست تیمی در فایل [docs/PROJECT_GUIDE.md](docs/PROJECT_GUIDE.md) موجود است.

-Laravel is accessible, powerful, and provides tools required for large, robust applications.
+## منابع مفید

-## Learning Laravel
+- [Laravel Documentation](https://laravel.com/docs)
+- [Livewire v3 Docs](https://livewire.laravel.com/)
+- [TailwindCSS Docs](https://tailwindcss.com/docs)
+- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
+- [nWidart Modules Docs](https://nwidart.com/laravel-modules/v11/introduction)

-Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.
+## مجوز

-You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.
-
-If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.
-
-## Laravel Sponsors
-
-We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).
-
-### Premium Partners
-
-- **[Vehikl](https://vehikl.com/)**
-- **[Tighten Co.](https://tighten.co)**
-- **[WebReinvent](https://webreinvent.com/)**
-- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-- **[64 Robots](https://64robots.com)**
-- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-- **[Cyber-Duck](https://cyber-duck.co.uk)**
-- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-- **[Jump24](https://jump24.co.uk)**
-- **[Redberry](https://redberry.international/laravel/)**
-- **[Active Logic](https://activelogic.com)**
-- **[byte5](https://byte5.de)**
-- **[OP.GG](https://op.gg)**
-
-## Contributing
-
-Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).
-
-## Code of Conduct
-
-In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).
-
-## Security Vulnerabilities
-
-If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.
-
-## License
-
-The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
+این پروژه تحت مجوز MIT عرضه می‌شود. برای اطلاعات بیشتر فایل `LICENSE` (در صورت وجود) را بررسی کنید.
