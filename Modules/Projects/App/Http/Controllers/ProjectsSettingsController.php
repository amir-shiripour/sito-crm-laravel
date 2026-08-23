<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectCategory;
use Modules\Projects\App\Http\Models\ProjectRole;
use Modules\Projects\App\Http\Models\ProjectSetting;
use Modules\Projects\App\Http\Models\ProjectStatus;

class ProjectsSettingsController extends Controller
{
    private const KEYS = [
        'projects_code_prefix',
        'projects_code_middle',
        'projects_code_suffix',
        'projects_code_padding',
        'projects_code_auto',
        'projects_default_category_id',
        'projects_default_status_id',
        'projects_auto_assign_creator',
        'projects_default_creator_role',
        'projects_default_member_role',
        'projects_require_client',
        'projects_require_dates',
        'projects_default_task_status_id',
        'projects_progress_mode',
        'projects_strict_task_due_dates',
        'projects_allow_members_to_create_tasks',
        'projects_document_max_size_mb',
        'projects_document_allowed_extensions',
        'projects_document_project_quota_mb',
        'projects_document_max_files_count',
        'projects_document_categories',
        'projects_notify_assigned_member',
        'projects_notify_task_assignment',
        'projects_notify_overdue_project',
        'projects_role_viewer_can_chat',
        'projects_role_viewer_can_comment',
        'projects_role_viewer_can_upload_doc',
        'projects_role_viewer_see_assigned_only',
        'projects_role_editor_see_assigned_only',
        'projects_only_show_assigned_tasks_to_members',
        'projects_role_editor_can_pin_messages',
        'projects_role_editor_can_delete_messages',
        'projects_role_editor_can_manage_phases',
        'projects_role_editor_can_delete_tasks',
    ];

    private const BOOLEANS = [
        'projects_code_auto',
        'projects_auto_assign_creator',
        'projects_require_client',
        'projects_require_dates',
        'projects_strict_task_due_dates',
        'projects_allow_members_to_create_tasks',
        'projects_notify_assigned_member',
        'projects_notify_task_assignment',
        'projects_notify_overdue_project',
        'projects_role_viewer_can_chat',
        'projects_role_viewer_can_comment',
        'projects_role_viewer_can_upload_doc',
        'projects_role_viewer_see_assigned_only',
        'projects_role_editor_see_assigned_only',
        'projects_only_show_assigned_tasks_to_members',
        'projects_role_editor_can_pin_messages',
        'projects_role_editor_can_delete_messages',
        'projects_role_editor_can_manage_phases',
        'projects_role_editor_can_delete_tasks',
    ];

    public function index()
    {
        $this->authorize('projects.settings.manage');

        $raw = ProjectSetting::allValues();

        $categories = ProjectCategory::active()->ordered()->get();
        $projectStatuses = ProjectStatus::forType('project')->get();
        $taskStatuses = ProjectStatus::forType('task')->get();

        $projectRoles = ProjectRole::orderBy('sort_order')->orderBy('id')->get();
        $availablePermissions = ProjectRole::availablePermissions();

        return view('projects::settings.index', compact(
            'raw',
            'categories',
            'projectStatuses',
            'taskStatuses',
            'projectRoles',
            'availablePermissions'
        ));
    }

    public function update(Request $request)
    {
        $this->authorize('projects.settings.manage');

        $rules = [
            'projects_code_prefix' => 'nullable|string|max:20',
            'projects_code_middle' => 'nullable|string|max:20',
            'projects_code_suffix' => 'nullable|string|max:20',
            'projects_code_padding' => 'nullable|integer|min:1|max:10',
            'projects_default_category_id' => 'nullable|exists:projects_categories,id',
            'projects_default_status_id' => 'nullable|exists:projects_statuses,id',
            'projects_default_creator_role' => 'nullable|string|max:50',
            'projects_default_member_role' => 'nullable|string|max:50',
            'projects_default_task_status_id' => 'nullable|exists:projects_statuses,id',
            'projects_progress_mode' => 'nullable|in:auto_tasks,manual',
            'projects_document_max_size_mb' => 'nullable|integer|min:1|max:500',
            'projects_document_allowed_extensions' => 'nullable|string|max:1000',
            'projects_document_project_quota_mb' => 'nullable|integer|min:10|max:10000',
            'projects_document_max_files_count' => 'nullable|integer|min:1|max:500',
            'projects_document_categories' => 'nullable|string|max:1000',
        ];

        $request->validate($rules);

        foreach (self::KEYS as $key) {
            $value = in_array($key, self::BOOLEANS, true)
                ? ($request->boolean($key) ? '1' : '0')
                : $request->input($key, '');

            ProjectSetting::set($key, $value);
        }

        return back()->with('success', 'تنظیمات ماژول پروژه‌ها با موفقیت ذخیره شد.')
            ->with('active_tab', $request->input('active_tab', 'numbering'));
    }

    public function previewCode(Request $request)
    {
        $prefix = $request->input('prefix', 'PRJ-');
        $middle = $request->input('middle', now()->format('Y'));
        $suffix = $request->input('suffix', '');
        $padding = max(1, (int)$request->input('padding', 4));

        $count = Project::withTrashed()->count() + 1;
        $middlePart = !empty($middle) ? $middle . '-' : '';

        return response()->json([
            'preview' => $prefix . $middlePart . str_pad($count, $padding, '0', STR_PAD_LEFT) . $suffix,
        ]);
    }

    public function seedStatuses()
    {
        $this->authorize('projects.settings.manage');

        try {
            Artisan::call('db:seed', [
                '--class' => 'Modules\Projects\Database\Seeders\ProjectStatusSeeder',
                '--force' => true,
            ]);
            return back()->with('success', 'وضعیت‌های پیش‌فرض پروژه با موفقیت بازنشانی و نصب شدند.')
                ->with('active_tab', 'automation');
        } catch (\Throwable $e) {
            return back()->with('error', 'خطا در نصب وضعیت‌ها: ' . $e->getMessage())
                ->with('active_tab', 'automation');
        }
    }
}
