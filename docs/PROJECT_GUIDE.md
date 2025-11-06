# راهنمای جامع پروژه Sito CRM (Laravel)

این سند برای توسعه‌دهندگان جدید و دستیارهای هوش مصنوعی تهیه شده تا بتوانند با حداقل زمان راه‌اندازی، به عنوان همکار تیمی روی پروژه **sito-crm-laravel** ادامه دهند. تمام بخش‌ها با تکیه بر بهترین پرکتیس‌های Laravel، اصول Clean Architecture، امنیت و قابلیت نگهداری تدوین شده‌اند.

---

## 1. نمای کلی

- **فریم‌ورک اصلی:** Laravel 10.x با PHP 8.1+
- **احراز هویت و UI پایه:** Laravel Jetstream + Livewire 3 + TailwindCSS (با افزونه RTL)
- **مدیریت مجوز:** Spatie Laravel Permission
- **سیستم ماژولار:** nwidart/laravel-modules (آماده برای افزودن ماژول‌های مستقل)
- **رابط کاربری:** Blade Components، TailwindCSS، Livewire
- **هدف پروژه:** CRM چندلایه با داشبورد ادمین، مدیریت کاربران، نقش‌ها، ماژول‌ها و مراحل نصب تعاملی.

---

## 2. وابستگی‌ها و ابزارها

### Backend (Composer)
- `laravel/framework`, `laravel/fortify`, `laravel/jetstream`, `laravel/sanctum`
- `livewire/livewire`
- `nwidart/laravel-modules`
- `spatie/laravel-permission`
- `blade-ui-kit/blade-heroicons`

### Frontend (Node)
- `vite` + `laravel-vite-plugin`
- `tailwindcss`, `@tailwindcss/forms`, `@tailwindcss/typography`, `tailwindcss-rtl`
- `axios`

### توسعه و تست
- `phpunit/phpunit`, `fakerphp/faker`, `laravel/pint`

---

## 3. ساختار دایرکتوری‌های کلیدی

```
app/
 ├── Actions/          # اکشن‌های Jetstream/Fortify
 ├── Console/
 ├── Exceptions/
 ├── Http/
 │    ├── Controllers/  # کنترلرهای صفحه، نصب و پنل ادمین
 │    ├── Middleware/   # میدل‌ورها شامل نصب/غیرفعال‌سازی
 │    └── Requests/     # FormRequest های ادمین
 ├── Models/            # مدل‌های Eloquent
 ├── Providers/         # Service Providers
 ├── Services/          # سرویس‌ها (مانند EnvWriter)
 └── View/Components/   # Blade Component های سفارشی

bootstrap/             # راه‌اندازی اپلیکیشن و کش‌ها
config/                # تنظیمات هسته + modules.php, permission.php
database/
 ├── factories/
 ├── migrations/
 └── seeders/           # RolePermissionSeeder, ModuleSeeder, ThemeSeeder
modules/               # محل ماژول‌های nwidart (فعلاً خالی)
public/
resources/
 ├── css/, js/          # ورودی‌های Vite
 └── views/
      ├── admin/        # Blade views پنل مدیریت
      ├── install/      # صفحات مراحل نصب
      ├── auth/, profile/ و ...
routes/
 ├── web.php            # صفحات عمومی + محافظت نصب
 ├── admin.php          # روت‌های پنل مدیریت (prefix admin)
 ├── api.php            # آماده برای API (Sanctum)
 └── ...
storage/
tests/
 ├── Feature/
 └── Unit/
```

---

## 4. جریان‌های اصلی کسب‌وکار

### 4.1 نصب اولیه (Installer)
- مسیرها در `routes/web.php` با prefix `install` و middleware `prevent.if.installed`.
- کنترلر اصلی: `App\Http\Controllers\InstallController`
- **گام‌ها:** اتصال پایگاه‌داده، ایجاد کاربر مدیر، تنظیمات پایه.
- سرویس `App\Services\EnvWriter` برای به‌روزرسانی امن فایل `.env` استفاده می‌شود.

### 4.2 محافظت از دسترسی قبل/بعد از نصب
- میدل‌ورهای `RedirectIfNotInstalled`, `RedirectIfInstalled`, `CheckInstallationStatus` در `app/Http/Middleware` مسئول هدایت مناسب کاربران هستند.

### 4.3 پنل ادمین
- فایل روت `routes/admin.php` با middleware `['web', 'auth']` و نقش‌ها.
- کنترلرها:
  - `Admin\DashboardController` برای داشبورد
  - `Admin\UserController` برای CRUD کاربران (با FormRequest ها)
  - `Admin\RoleController` برای نقش‌ها و مجوزها (Spatie)
  - `Admin\ModuleController` برای مدیریت وضعیت ماژول‌ها (فعال/غیرفعال)
- ویوهای Blade در `resources/views/admin/**` و partial ها در `resources/views/admin/partials`.

### 4.4 نقش‌ها و مجوزها
- استفاده از جدول‌های Spatie (مهاجرت `database/migrations/2025_10_24_143620_create_permission_tables.php`).
- Seeder `RolePermissionSeeder` نقش‌های پیش‌فرض (مثلاً super-admin) و مجوزها را ثبت می‌کند.
- Middleware `role:` برای کنترل سطح دسترسی روی روت‌ها به کار رفته است.

### 4.5 ماژولار بودن
- ساخت ماژول جدید: `php artisan module:make Blog`
- وضعیت ماژول‌ها در `modules_statuses.json` ذخیره می‌شود و `ModuleController` آنها را مدیریت می‌کند.
- مسیر ذخیره‌سازی ماژول‌ها در `config/modules.php` تعریف شده (`Modules/`).
- برای بارگذاری سرویس‌های هر ماژول، Service Provider اختصاصی داخل خود ماژول ثبت کنید.

---

## 5. فرآیند راه‌اندازی محیط توسعه

1. **پیش‌نیازها:** PHP 8.1، Composer 2، Node 18+، NPM یا PNPM، MySQL/MariaDB یا PostgreSQL.
2. `cp .env.example .env` و مقداردهی متغیرهای زیر:
   - `APP_NAME`, `APP_URL`
   - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_DRIVER`
   - تنظیمات ایمیل برای بازیابی رمز (اختیاری)
3. `composer install`
4. `php artisan key:generate`
5. `npm install`
6. `php artisan migrate --seed`
7. (اختیاری) `php artisan storage:link`
8. اجرای سرور:
   - Backend: `php artisan serve`
   - Frontend: `npm run dev`

> کاربر مدیر پیش‌فرض توسط Seeder ساخته می‌شود؛ اطلاعات را در Seeder بررسی کنید یا از Installer استفاده نمایید.

---

## 6. استانداردهای کدنویسی و معماری

- پیروی از **PSR-12** و استفاده از `laravel/pint` برای فرمت خودکار (`./vendor/bin/pint`).
- اعتبارسنجی ورودی‌ها حتماً با **FormRequest** در `app/Http/Requests` انجام شود.
- مدیریت خطا و پاسخ JSON در کنترلرها از طریق Exception مرکزی (`app/Exceptions/Handler.php`).
- استفاده از **Service Layer** برای منطق پیچیده (پوشه `app/Services`) و **Repository Pattern** در صورت نیاز (پوشه پیشنهادی `app/Repositories`).
- رعایت اصل **Single Responsibility**: کنترلرها سبک، سرویس‌ها مسئول منطق، مدل‌ها برای Eloquent.
- محافظت در برابر XSS با استفاده از خروجی امن Blade (`{{ }}`) و Escape ورودی.
- CSRF به صورت پیش‌فرض فعال است؛ برای API ها از Sanctum و توکن استفاده شود.

---

## 7. الگوی توسعه Feature

1. ایجاد شاخه Git جدید (`feature/<short-name>`).
2. تعریف Contract/Interface در صورت نیاز و پیاده‌سازی در Service/Repository.
3. افزودن Request جدید برای اعتبارسنجی.
4. نوشتن تست Feature/Unit قبل یا بعد از پیاده‌سازی (TDD ترجیح داده می‌شود).
5. اجرای `php artisan test` و `npm run build` (در صورت تغییر فرانت).
6. ثبت مستندات مختصر تغییرات در بخش مرتبط فایل راهنما در صورت اضافه شدن قابلیت مهم.
7. Pull Request با توضیحات و چک‌لیست انجام شده.

---

## 8. پایگاه‌داده و Seeders

- تمام مهاجرت‌ها در `database/migrations` نگهداری می‌شوند؛ تاریخ‌ها نشان‌دهنده توالی مورد انتظار هستند.
- **Seederهای کلیدی:**
  - `DatabaseSeeder` فراخوانی کننده seederهای دیگر.
  - `RolePermissionSeeder` ایجاد نقش‌ها و مجوزهای پایه.
  - `ModuleSeeder` ثبت ماژول‌های پیش‌فرض در جدول `modules`.
  - `ThemeSeeder` ثبت تم‌ها و نیازمندی‌های مربوط (مرتبط با جدول `themes` و `theme_module_requirements`).
- اجرای seeder: `php artisan db:seed` یا `php artisan migrate --seed`.
- برای اضافه کردن داده تست جدید، seeder اختصاصی بسازید و آن را در `DatabaseSeeder` فراخوانی کنید.

---

## 9. منابع Frontend

- فایل‌های ورودی Vite در `resources/js/app.js` و `resources/css/app.css`.
- تنظیمات Tailwind در `tailwind.config.js`، شامل فعال‌سازی RTL.
- Blade layout اصلی در `resources/views/layouts/app.blade.php` (برای صفحات عمومی) و `resources/views/admin/layout.blade.php` (در صورت وجود) بررسی شود.
- برای کامپوننت‌های Livewire از ساختار `php artisan make:livewire Example` و نگهداری فایل‌های کلاس در `app/Livewire` (در صورت ایجاد) و ویو در `resources/views/livewire` استفاده کنید.

---

## 10. مدیریت ماژول‌ها

- فایل `modules_statuses.json` وضعیت فعال/غیرفعال ماژول‌ها را نگه می‌دارد.
- برای نصب ماژول موجود: `php artisan module:enable ModuleName` و برای غیرفعال‌سازی `php artisan module:disable ModuleName`.
- برای انتشار منابع ماژول (ویو، کانفیگ، مهاجرت): `php artisan module:publish ModuleName`.
- هر ماژول باید شامل Service Provider اختصاصی، فایل‌های روت، ویو، مدل و مهاجرت خود باشد تا از تداخل با هسته جلوگیری شود.

---

## 11. مدیریت مجوز و نقش

- نقش‌ها در جدول `roles` و مجوزها در `permissions` ذخیره می‌شوند.
- استفاده از Trait `HasRoles` در مدل `App\Models\User`.
- برای بررسی نقش در کد:
  ```php
  if ($user->hasRole('super-admin')) { ... }
  ```
- برای مجوزها:
  ```php
  $this->authorize('view users');
  ```
- تعریف Role/Permission جدید:
  1. ایجاد migration یا seeder جدید یا استفاده از Tinker.
  2. بروزرسانی رابط کاربری برای مدیریت جدید.

---

## 12. لاگ‌گیری و مانیتورینگ

- کانال‌های لاگ در `config/logging.php`؛ به صورت پیش‌فرض `stack` (شامل daily) فعال است.
- برای پیاده‌سازی نظارت بر رویدادها، می‌توانید از رویدادهای Laravel یا jobs استفاده کنید.
- در محیط تولید، پیشنهاد می‌شود از Sentry/Bugsnag استفاده شود (نیاز به نصب بسته مجزا).

---

## 13. تست و تضمین کیفیت

- تست‌ها در `tests/Feature` و `tests/Unit` نگهداری می‌شوند.
- اجرای کلی تست‌ها: `php artisan test` یا `./vendor/bin/phpunit`.
- تست‌های فرانت: در صورت نیاز از `vitest` یا `jest` استفاده کنید (هنوز اضافه نشده).
- اجرای lint:
  - PHP: `./vendor/bin/pint`
  - Tailwind/Vite: `npm run build` برای اطمینان از عدم وجود خطا.
- قبل از Merge، چک‌لیست زیر را انجام دهید:
  - [ ] تست‌های اتوماتیک سبز
  - [ ] پوشش validation و authorization بررسی شده
  - [ ] مستندسازی (در صورت نیاز) به‌روزرسانی شده

---

## 14. استقرار (Deployment) پیشنهادی

1. اجرای `composer install --optimize-autoloader --no-dev`
2. اجرای `npm run build`
3. `php artisan migrate --force`
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. تنظیم Supervisor برای queue worker در صورت استفاده از صف.

---

## 15. پیوست‌ها و منابع تکمیلی

- فایل `routes.txt` شامل یادداشت‌ها یا مسیرهای جانبی است (در صورت نیاز بررسی شود).
- برای توسعه ماژول‌های جدید، مستندات رسمی nwidart را بخوانید و در صورت پیاده‌سازی repository/service جداگانه، مسیر `app/Repositories` و `app/Services` را رعایت کنید.
- هر تغییری که روی سیستم نصب‌کننده، نقش‌ها یا ساختار دیتابیس انجام می‌شود باید با بروزرسانی این سند همراه باشد.

---

## 16. چک‌لیست ورود عضو جدید تیم

- [ ] مطالعه این سند و README
- [ ] راه‌اندازی محیط محلی طبق بخش 5
- [ ] اجرای `php artisan test`
- [ ] اجرای `npm run dev`
- [ ] مرور ساختار روت‌ها (`routes/web.php`, `routes/admin.php`)
- [ ] بررسی Seederها برای آشنایی با داده اولیه
- [ ] مرور کنترلرهای ادمین برای درک جریان‌های اصلی
- [ ] هماهنگی با لید تیم برای دریافت دسترسی‌های محیط‌های مشترک

---

با دنبال کردن این راهنما، هر توسعه‌دهنده یا دستیار هوش مصنوعی می‌تواند با درک مناسب از معماری، فرآیندها و استانداردهای پروژه، در توسعه Sito CRM مشارکت مؤثر داشته باشد.
