<?php
// app/Support/PermissionCatalog.php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionCatalog
{
    /**
     * تعریف گروه‌ها و الگوهای تشخیص (بر اساس پیشوند/وایلدکارد ساده)
     * key = شناسه گروه، title = تیتر فارسی، matchers = الگوهای شروع (prefix)
     */
    public static function groups(): array
    {
        return [
            'users' => [
                'title'    => 'کاربران',
                'matchers' => ['users.', 'menu.see.users'],
            ],
            'roles' => [
                'title'    => 'نقش‌ها و دسترسی‌ها',
                'matchers' => ['roles.', 'menu.see.roles', 'roles.assign-permissions'],
            ],
            'custom_fields' => [
                'title'    => 'فیلدهای سفارشی',
                'matchers' => ['custom-fields.', 'menu.see.custom-fields'],
            ],
            'menus' => [
                'title'    => 'منوها',
                'matchers' => ['menu.'],
            ],
            'clients' => [
                'title'    => 'مشتریان',
                'matchers' => ['clients.'],
            ],
            'client_calls' => [
                'title'    => 'تماس‌های مشتریان',
                'matchers' => ['client-calls.'],
            ],
            'followups' => [
                'title'    => 'پیگیری‌ها (FollowUps)',
                'matchers' => ['followups.'],
            ],
            'reminders' => [
                'title'    => 'یادآوری‌ها (Reminders)',
                'matchers' => ['reminders.'],
            ],
            'sms' => [
                'title'    => 'پیامک (SMS)',
                'matchers' => ['sms.'],
            ],
            'tasks' => [
                'title'    => 'وظایف (Tasks)',
                'matchers' => ['tasks.'],
            ],
            'workflows' => [
                'title'    => 'گردش کار (Workflows)',
                'matchers' => ['workflows.'],
            ],
            'modules' => [
                'title'    => 'مدیریت ماژول‌ها',
                'matchers' => ['modules.'],
            ],

            // Booking module (based on Modules/Booking/Installer.php)
            'booking' => [
                'title'    => 'نوبت‌دهی (Booking)',
                'matchers' => ['booking.view', 'booking.manage'],
            ],
            'booking_settings' => [
                'title'    => 'نوبت‌دهی: تنظیمات',
                'matchers' => ['booking.settings.'],
            ],
            'booking_categories' => [
                'title'    => 'نوبت‌دهی: دسته‌بندی‌ها',
                'matchers' => ['booking.categories.'],
            ],
            'booking_forms' => [
                'title'    => 'نوبت‌دهی: فرم‌ها',
                'matchers' => ['booking.forms.'],
            ],
            'booking_services' => [
                'title'    => 'نوبت‌دهی: سرویس‌ها',
                'matchers' => ['booking.services.'],
            ],
            'booking_availability' => [
                'title'    => 'نوبت‌دهی: ظرفیت/دسترس‌پذیری',
                'matchers' => ['booking.availability.'],
            ],
            'booking_appointments' => [
                'title'    => 'نوبت‌دهی: نوبت‌ها',
                'matchers' => ['booking.appointments.'],
            ],
            'booking_reports' => [
                'title'    => 'نوبت‌دهی: گزارش‌ها',
                'matchers' => ['booking.reports.'],
            ],

            // Properties module
            'properties' => [
                'title'    => 'املاک',
                'matchers' => ['properties.view', 'properties.create', 'properties.edit', 'properties.delete', 'properties.manage'],
            ],
            'properties_settings' => [
                'title'    => 'املاک: تنظیمات',
                'matchers' => ['properties.settings.'],
            ],
            'properties_categories' => [
                'title'    => 'املاک: دسته‌بندی‌ها',
                'matchers' => ['properties.categories.'],
            ],
            'properties_attributes' => [
                'title'    => 'املاک: ویژگی‌ها و امکانات',
                'matchers' => ['properties.attributes.'],
            ],
            'properties_owners' => [
                'title'    => 'املاک: مالکین',
                'matchers' => ['properties.owners.'],
            ],

            'other' => [
                'title'    => 'سایر',
                'matchers' => [''], // fallback
            ],
        ];
    }

    /**
     * ترجمه فارسی هر permission key
     */
    public static function translate(string $name): string
    {
        static $map = [
            // Users
            'users.view'           => 'مشاهده کاربران',
            'users.create'         => 'ایجاد کاربر',
            'users.update'         => 'ویرایش کاربر',
            'users.delete'         => 'حذف کاربر',
            'users.assign-roles'   => 'تخصیص نقش به کاربر',

            // Roles
            'roles.view'               => 'مشاهده نقش‌ها',
            'roles.create'             => 'ایجاد نقش',
            'roles.update'             => 'ویرایش نقش',
            'roles.delete'             => 'حذف نقش',
            'roles.assign-permissions' => 'تخصیص مجوز به نقش',

            // Menus
            'menu.see.users'           => 'نمایش منوی کاربران',
            'menu.see.roles'           => 'نمایش منوی نقش‌ها',

            // Custom Fields
            'menu.see.custom-fields'   => 'نمایش منوی فیلدهای سفارشی',
            'custom-fields.view'       => 'مشاهده فیلدها',
            'custom-fields.create'     => 'ایجاد فیلد',
            'custom-fields.update'     => 'ویرایش فیلد',
            'custom-fields.delete'     => 'حذف فیلد',

            // Clients
            'clients.manage'         => 'مدیریت مشتریان',
            'clients.view'           => 'مشاهده مشتریان',
            'clients.view.all'       => 'مشاهده همه مشتریان',
            'clients.view.assigned'  => 'مشاهده مشتریان مرتبط',
            'clients.view.own'       => 'مشاهده مشتریان ایجاد شده توسط خود',
            'clients.create'         => 'ایجاد مشتری',
            'clients.edit'           => 'ویرایش مشتری',
            'clients.delete'         => 'حذف مشتری',

            // Client Calls
            'client-calls.manage'        => 'مدیریت تماس‌های مشتریان',
            'client-calls.view'          => 'مشاهده تماس‌های مشتریان',
            'client-calls.view.all'      => 'مشاهده همه تماس‌های مشتریان',
            'client-calls.view.assigned' => 'مشاهده تماس‌های مشتریان مرتبط',
            'client-calls.view.own'      => 'مشاهده تماس‌های ثبت‌شده توسط خود',
            'client-calls.create'        => 'ثبت تماس جدید',
            'client-calls.edit'          => 'ویرایش تماس‌ها',
            'client-calls.delete'        => 'حذف تماس‌ها',

            // FollowUps
            'followups.manage'        => 'مدیریت پیگیری‌ها',
            'followups.view'          => 'مشاهده پیگیری‌ها',
            'followups.view.all'      => 'مشاهده همه پیگیری‌ها',
            'followups.view.assigned' => 'مشاهده پیگیری‌های مرتبط',
            'followups.view.own'      => 'مشاهده پیگیری‌های ثبت‌شده توسط خود',
            'followups.create'        => 'ثبت پیگیری جدید',
            'followups.edit'          => 'ویرایش پیگیری‌ها',
            'followups.delete'        => 'حذف پیگیری‌ها',

            // Reminders
            'reminders.manage'        => 'مدیریت یادآوری‌ها',
            'reminders.view'          => 'مشاهده یادآوری‌ها',
            'reminders.view.all'      => 'مشاهده همه یادآوری‌ها',
            'reminders.view.assigned' => 'مشاهده یادآوری‌های مرتبط',
            'reminders.view.own'      => 'مشاهده یادآوری‌های ثبت‌شده توسط خود',
            'reminders.create'        => 'ثبت یادآوری جدید',
            'reminders.edit'          => 'ویرایش یادآوری‌ها',
            'reminders.delete'        => 'حذف یادآوری‌ها',

            // SMS
            'sms.manage'              => 'مدیریت پیامک‌ها',
            'sms.messages.view'       => 'مشاهده پیامک‌ها',
            'sms.messages.view.all'   => 'مشاهده همه پیامک‌ها',
            'sms.messages.view.own'   => 'مشاهده پیامک‌های خود',
            'sms.messages.create'     => 'ارسال پیامک',
            'sms.messages.delete'     => 'حذف پیامک‌ها',
            'sms.templates.manage'    => 'مدیریت قالب‌های پیامک',
            'sms.settings.manage'     => 'مدیریت تنظیمات پیامک',

            // Tasks
            'tasks.manage'            => 'مدیریت وظایف',
            'tasks.view'              => 'مشاهده وظایف',
            'tasks.view.all'          => 'مشاهده همه وظایف',
            'tasks.view.assigned'     => 'مشاهده وظایف محول شده',
            'tasks.view.own'          => 'مشاهده وظایف خود',
            'tasks.create'            => 'ایجاد وظیفه',
            'tasks.edit'              => 'ویرایش وظیفه',
            'tasks.delete'            => 'حذف وظیفه',

            // Workflows
            'workflows.manage'        => 'مدیریت گردش کار',
            'workflows.view'          => 'مشاهده گردش کارها',
            'workflows.create'        => 'ایجاد گردش کار',
            'workflows.edit'          => 'ویرایش گردش کار',
            'workflows.delete'        => 'حذف گردش کار',

            // Modules
            'modules.manage'          => 'مدیریت ماژول‌ها (نصب/حذف)',

            // Booking (Modules/Booking/Installer.php)
            'booking.view'                 => 'مشاهده ماژول نوبت‌دهی',
            'booking.manage'               => 'مدیریت کامل نوبت‌دهی',

            'booking.settings.manage'      => 'مدیریت تنظیمات نوبت‌دهی',

            'booking.categories.view'      => 'مشاهده دسته‌بندی‌های نوبت‌دهی',
            'booking.categories.create'    => 'ایجاد دسته‌بندی نوبت‌دهی',
            'booking.categories.edit'      => 'ویرایش دسته‌بندی نوبت‌دهی',
            'booking.categories.delete'    => 'حذف دسته‌بندی نوبت‌دهی',
            'booking.categories.manage'    => 'مدیریت دسته‌بندی‌های نوبت‌دهی',

            'booking.forms.view'           => 'مشاهده فرم‌های نوبت‌دهی',
            'booking.forms.create'         => 'ایجاد فرم نوبت‌دهی',
            'booking.forms.edit'           => 'ویرایش فرم نوبت‌دهی',
            'booking.forms.delete'         => 'حذف فرم نوبت‌دهی',
            'booking.forms.manage'         => 'مدیریت فرم‌های نوبت‌دهی',

            'booking.services.view'        => 'مشاهده سرویس‌های نوبت‌دهی',
            'booking.services.create'      => 'ایجاد سرویس نوبت‌دهی',
            'booking.services.edit'        => 'ویرایش سرویس نوبت‌دهی',
            'booking.services.delete'      => 'حذف سرویس نوبت‌دهی',
            'booking.services.manage'      => 'مدیریت سرویس‌های نوبت‌دهی',

            'booking.availability.manage'  => 'مدیریت ظرفیت/دسترس‌پذیری نوبت‌دهی',

            'booking.appointments.view'     => 'مشاهده نوبت‌ها',
            'booking.appointments.view.all' => 'مشاهده همه نوبت‌ها',
            'booking.appointments.view.own' => 'مشاهده نوبت‌های ثبت‌شده توسط خود',
            'booking.appointments.create'   => 'ایجاد/ثبت نوبت جدید',
            'booking.appointments.edit'     => 'ویرایش نوبت',
            'booking.appointments.cancel'   => 'لغو نوبت',
            'booking.appointments.manage'   => 'مدیریت نوبت‌ها',

            'booking.reports.view'          => 'مشاهده گزارش‌های نوبت‌دهی',

            // Properties
            'properties.view'               => 'مشاهده املاک',
            'properties.view.all'           => 'مشاهده همه املاک',
            'properties.view.own'           => 'مشاهده املاک خود',
            'properties.create'             => 'ایجاد ملک',
            'properties.edit'               => 'ویرایش ملک',
            'properties.edit.all'           => 'ویرایش همه املاک',
            'properties.edit.own'           => 'ویرایش املاک خود',
            'properties.delete'             => 'حذف ملک',
            'properties.delete.all'         => 'حذف همه املاک',
            'properties.delete.own'         => 'حذف املاک خود',
            'properties.manage'             => 'مدیریت کامل املاک',

            'properties.settings.manage'    => 'مدیریت تنظیمات املاک',

            'properties.categories.view'    => 'مشاهده دسته‌بندی‌های املاک',
            'properties.categories.create'  => 'ایجاد دسته‌بندی املاک',
            'properties.categories.edit'    => 'ویرایش دسته‌بندی املاک',
            'properties.categories.delete'  => 'حذف دسته‌بندی املاک',
            'properties.categories.manage'  => 'مدیریت دسته‌بندی‌های املاک',

            'properties.attributes.view'    => 'مشاهده ویژگی‌ها و امکانات',
            'properties.attributes.create'  => 'ایجاد ویژگی/امکانات',
            'properties.attributes.edit'    => 'ویرایش ویژگی/امکانات',
            'properties.attributes.delete'  => 'حذف ویژگی/امکانات',
            'properties.attributes.manage'  => 'مدیریت ویژگی‌ها و امکانات',

            'properties.owners.view'        => 'مشاهده مالکین',
            'properties.owners.create'      => 'ایجاد مالک',
            'properties.owners.edit'        => 'ویرایش مالک',
            'properties.owners.delete'      => 'حذف مالک',
            'properties.owners.manage'      => 'مدیریت مالکین',
        ];

        // اگر ترجمه صریح نداریم، یک تبدیل خوانا بساز:
        if (!isset($map[$name])) {
            $tmp = str_replace(['.', '-', '_'], ' ', $name);
            return trim($tmp);
        }

        return $map[$name];
    }

    /**
     * تشخیص گروه برای یک نام مجوز
     */
    public static function detectGroup(string $name): string
    {
        foreach (self::groups() as $key => $g) {
            foreach ($g['matchers'] as $prefix) {
                if ($prefix === '' || str_starts_with($name, $prefix)) {
                    return $key;
                }
            }
        }
        return 'other';
    }

    /**
     * خروجی نهایی برای ویو: [
     *   groupKey => ['title' => ..., 'items' => [ ['name'=>'users.view','label'=>'مشاهده کاربران'], ... ] ]
     * ]
     */
    public static function groupAndTranslate(Collection $permissions): array
    {
        $out = [];
        foreach (self::groups() as $k => $g) {
            $out[$k] = ['title' => $g['title'], 'items' => []];
        }

        // دریافت کاربر جاری برای بررسی نقش سوپر ادمین
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole('super-admin');

        foreach ($permissions as $perm) {
            /** @var Permission $perm */
            $name  = $perm->name;

            // اگر مجوز modules.manage است و کاربر سوپر ادمین نیست، آن را نادیده بگیر
            if ($name === 'modules.manage' && !$isSuperAdmin) {
                continue;
            }

            $label = self::translate($name);
            $group = self::detectGroup($name);

            $out[$group]['items'][] = ['name' => $name, 'label' => $label];
        }

        // گروه‌هایی که خالی مانده‌اند را حذف کن
        foreach ($out as $k => $g) {
            if (empty($g['items'])) {
                unset($out[$k]);
            }
        }

        // مرتب‌سازی داخلی هر گروه بر اساس برچسب فارسی
        foreach ($out as &$g) {
            usort($g['items'], fn($a, $b) => strcmp($a['label'], $b['label']));
        }

        return $out;
    }
}
