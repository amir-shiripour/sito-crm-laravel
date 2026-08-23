<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Projects\App\Http\Models\ProjectDocument;
use Modules\Projects\App\Http\Models\ProjectRole;

class ProjectsRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $docCategories = ProjectDocument::getCategories();
        $docCategoryPerms = array_map(fn($c) => 'documents.category.' . $c, $docCategories);

        $roles = [
            [
                'name' => 'manager',
                'display_name' => 'مدیر پروژه',
                'description' => 'دسترسی کامل به مدیریت پروژه، فازها، وظایف، تنظیمات، اسناد، پیام‌ها و اعضا',
                'color' => 'purple',
                'icon' => 'crown',
                'is_system' => true,
                'permissions' => array_merge([
                    'projects.view',
                    'projects.edit',
                    'projects.status',
                    'projects.duplicate',
                    'projects.cancel',
                    'phases.manage',
                    'phases.delete',
                    'tasks.create',
                    'tasks.edit',
                    'tasks.delete',
                    'tasks.assign',
                    'tasks.change_status',
                    'messages.view',
                    'messages.send',
                    'messages.pin',
                    'messages.delete',
                    'comments.view',
                    'comments.send',
                    'comments.delete',
                    'documents.view',
                    'documents.upload',
                    'documents.delete',
                    'activities.view'
                ], $docCategoryPerms),
                'sort_order' => 1,
            ],
            [
                'name' => 'editor',
                'display_name' => 'ویرایشگر',
                'description' => 'تعریف و مدیریت فازها، وظایف، کامنت‌ها و مشارکت فعال در گفتگو و اسناد پروژه',
                'color' => 'amber',
                'icon' => 'pencil',
                'is_system' => true,
                'permissions' => array_merge([
                    'projects.view',
                    'phases.manage',
                    'tasks.create',
                    'tasks.edit',
                    'tasks.assign',
                    'tasks.change_status',
                    'messages.view',
                    'messages.send',
                    'messages.pin',
                    'comments.view',
                    'comments.send',
                    'documents.view',
                    'documents.upload',
                    'activities.view'
                ], $docCategoryPerms),
                'sort_order' => 2,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'ناظر',
                'description' => 'مشاهده پیشرفت پروژه، انجام کارهای محوله، ثبت کامنت و گفتگو',
                'color' => 'blue',
                'icon' => 'eye',
                'is_system' => true,
                'permissions' => array_merge([
                    'projects.view',
                    'tasks.change_status',
                    'tasks.see_assigned_only',
                    'messages.view',
                    'messages.send',
                    'comments.view',
                    'comments.send',
                    'documents.view',
                    'activities.view'
                ], $docCategoryPerms),
                'sort_order' => 3,
            ],
        ];

        foreach ($roles as $roleData) {
            ProjectRole::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
