<?php

namespace Modules\Projects\App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRole extends Model
{
    protected $table = 'projects_roles';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'icon',
        'is_system',
        'permissions',
        'sort_order',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Check if this role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $perms = $this->permissions ?? [];
        return in_array($permission, $perms, true);
    }

    /**
     * Check if this role can view a specific document category
     */
    public function canViewDocumentCategory(?string $category): bool
    {
        if (!$this->hasPermission('documents.view')) {
            return false;
        }

        if (empty($category)) {
            return true;
        }

        $perms = $this->permissions ?? [];
        return in_array('documents.category.' . $category, $perms, true);
    }

    /**
     * Get grouped permission catalog with definitions
     */
    public static function availablePermissions(): array
    {
        $docCategoryItems = [];
        $docCategories = ProjectDocument::getCategories();
        foreach ($docCategories as $cat) {
            $catKey = 'documents.category.' . $cat;
            $docCategoryItems[$catKey] = [
                'label' => 'دسترسی به اسناد: ' . $cat,
                'desc' => 'امکان مشاهده، دانلود و دسترسی به اسناد و فایل‌های بخش «' . $cat . '»',
                'category_name' => $cat,
            ];
        }

        $catalog = [
            'project' => [
                'title' => 'مدیریت پروژه',
                'icon' => 'folder',
                'color' => 'indigo',
                'items' => [
                    'projects.view' => ['label' => 'مشاهده اطلاعات پروژه', 'desc' => 'دسترسی به مشاهده خلاصه، اعضا و مشخصات پروژه'],
                    'projects.edit' => ['label' => 'ویرایش اطلاعات پایه', 'desc' => 'تغییر عنوان، توضیحات، تاریخ‌ها و دسته‌بندی پروژه'],
                    'projects.status' => ['label' => 'تغییر وضعیت پروژه', 'desc' => 'تغییر گام پروژه (شروع، در حال انجام، تکمیل، تعویق)'],
                    'projects.cancel' => ['label' => 'لغو پروژه', 'desc' => 'امکان لغو و متوقف کردن پروژه'],
                ],
            ],
            'templates' => [
                'title' => 'الگوهای فاز و کار',
                'icon' => 'template',
                'color' => 'amber',
                'items' => [
                    'templates.manage' => ['label' => 'مدیریت و ذخیره الگوها', 'desc' => 'ساخت، ویرایش و ذخیره ساختار فازها و کارها به عنوان الگوی آماده'],
                    'templates.apply' => ['label' => 'بارگذاری و اعمال الگو', 'desc' => 'درون‌ریزی و اعمال ساختار الگوها در این پروژه'],
                ],
            ],
            'phases' => [
                'title' => 'فازها و گروه‌بندی',
                'icon' => 'layers',
                'color' => 'purple',
                'items' => [
                    'phases.manage' => ['label' => 'مدیریت فازها و گروه‌ها', 'desc' => 'ایجاد، ویرایش نام، تعیین سرپرست و جابجایی فازها'],
                    'phases.delete' => ['label' => 'حذف فازها', 'desc' => 'امکان حذف کامل یک فاز به همراه گروه‌های داخل آن'],
                ],
            ],
            'tasks' => [
                'title' => 'کارها و چک‌لیست‌ها',
                'icon' => 'check-circle',
                'color' => 'emerald',
                'items' => [
                    'tasks.create' => ['label' => 'افزودن کار جدید', 'desc' => 'تعریف کار یا چک‌لیست جدید در فازها'],
                    'tasks.edit' => ['label' => 'ویرایش کارها', 'desc' => 'تغییر عنوان، توضیحات، اولویت و سررسید کار'],
                    'tasks.delete' => ['label' => 'حذف کارها', 'desc' => 'امکان حذف کارهای ثبت‌شده'],
                    'tasks.assign' => ['label' => 'تعیین و تغییر مسئول', 'desc' => 'اختصاص کار به اعضای تیم پروژه'],
                    'tasks.change_status' => ['label' => 'تغییر وضعیت / تکمیل کار', 'desc' => 'تیک زدن و تغییر مرحله انجام کار'],
                    'tasks.see_assigned_only' => ['label' => 'محدودیت: فقط کارهای من', 'desc' => 'کاربر تنها کارهایی که مسئول آن است را در لیست مشاهده کند'],
                ],
            ],
            'messages' => [
                'title' => 'گفتگو و پیام‌ها',
                'icon' => 'chat',
                'color' => 'blue',
                'items' => [
                    'messages.view' => ['label' => 'مشاهده تب گفتگو', 'desc' => 'دسترسی به تاریخچه چت و یادداشت‌های هماهنگی'],
                    'messages.send' => ['label' => 'ارسال پیام جدید', 'desc' => 'نوشتن پیام، منشن کردن اعضا و ریپلای'],
                    'messages.pin' => ['label' => 'پین و سنجاق کردن پیام', 'desc' => 'سنجاق کردن پیام‌های کلیدی در بالای چت'],
                    'messages.delete' => ['label' => 'حذف پیام‌های دیگران', 'desc' => 'مدیریت و حذف پیام‌های ارسالی سایر کاربران'],
                ],
            ],
            'comments' => [
                'title' => 'کامنت‌ها و بازخوردها',
                'icon' => 'annotation',
                'color' => 'amber',
                'items' => [
                    'comments.view' => ['label' => 'مشاهده کامنت‌ها', 'desc' => 'دیدن نظرات و بازخوردهای ثبت‌شده روی هر کار'],
                    'comments.send' => ['label' => 'ثبت کامنت جدید', 'desc' => 'ارسال نظر، راهنمایی و بازخورد روی کارها'],
                    'comments.delete' => ['label' => 'حذف کامنت‌های دیگران', 'desc' => 'امکان حذف کامنت‌های ثبت‌شده توسط سایر اعضا'],
                ],
            ],
            'documents' => [
                'title' => 'اسناد و فایل‌ها',
                'icon' => 'document',
                'color' => 'rose',
                'items' => [
                    'documents.view' => ['label' => 'مشاهده عمومی تب اسناد', 'desc' => 'دسترسی کلی به فایل‌ها، پیوست‌ها و لینک‌های پروژه'],
                    'documents.upload' => ['label' => 'آپلود سند جدید', 'desc' => 'بارگذاری فایل و افزودن پیوند جدید به پروژه'],
                    'documents.delete' => ['label' => 'حذف اسناد', 'desc' => 'امکان حذف فایل‌ها و پیوست‌های پروژه'],
                ],
            ],
        ];

        if (!empty($docCategoryItems)) {
            $catalog['document_categories'] = [
                'title' => 'دسته‌بندی‌های مجاز اسناد',
                'icon' => 'folder',
                'color' => 'teal',
                'items' => $docCategoryItems,
            ];
        }

        $catalog['activity'] = [
            'title' => 'تاریخچه و رویدادها',
            'icon' => 'clock',
            'color' => 'teal',
            'items' => [
                'activities.view' => ['label' => 'مشاهده تاریخچه فعالیت‌ها', 'desc' => 'دسترسی به تب لاگ فعالیت‌ها، تغییرات و رویدادهای زمانی پروژه'],
            ],
        ];

        return $catalog;
    }

    /**
     * Color classes mapping for badges and UI
     */
    public function colorClasses(): array
    {
        $map = [
            'purple' => ['badge' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200/60 dark:border-purple-800/40', 'dot' => 'bg-purple-500', 'btn' => 'bg-purple-600'],
            'amber' => ['badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200/60 dark:border-amber-800/40', 'dot' => 'bg-amber-500', 'btn' => 'bg-amber-600'],
            'blue' => ['badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200/60 dark:border-blue-800/40', 'dot' => 'bg-blue-500', 'btn' => 'bg-blue-600'],
            'emerald' => ['badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/40', 'dot' => 'bg-emerald-500', 'btn' => 'bg-emerald-600'],
            'indigo' => ['badge' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200/60 dark:border-indigo-800/40', 'dot' => 'bg-indigo-500', 'btn' => 'bg-indigo-600'],
            'rose' => ['badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200/60 dark:border-rose-800/40', 'dot' => 'bg-rose-500', 'btn' => 'bg-rose-600'],
            'teal' => ['badge' => 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 border-teal-200/60 dark:border-teal-800/40', 'dot' => 'bg-teal-500', 'btn' => 'bg-teal-600'],
            'cyan' => ['badge' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300 border-cyan-200/60 dark:border-cyan-800/40', 'dot' => 'bg-cyan-500', 'btn' => 'bg-cyan-600'],
            'orange' => ['badge' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 border-orange-200/60 dark:border-orange-800/40', 'dot' => 'bg-orange-500', 'btn' => 'bg-orange-600'],
            'gray' => ['badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700', 'dot' => 'bg-gray-400', 'btn' => 'bg-gray-600'],
        ];

        return $map[$this->color] ?? $map['indigo'];
    }

    /**
     * Available SVG icons list for role builder
     */
    public static function availableIcons(): array
    {
        return [
            'crown' => 'تاج / مدیریت',
            'pencil' => 'مداد / ویرایشگر',
            'eye' => 'چشم / ناظر',
            'code' => 'کد / برنامه‌نویس',
            'palette' => 'پالت / طراح',
            'bug' => 'تست / کنترل کیفیت',
            'shield' => 'سپر / امنیت و سرپرست',
            'briefcase' => 'کیف / کارشناس',
            'user' => 'کاربر / همکار',
            'star' => 'ستاره / مدیر ارشد',
        ];
    }
}
